<?php

namespace App\Services;

use App\Models\DecisionCase;
use App\Models\Criterion;
use App\Models\Alternative;

class ElectreService
{
    protected $decisionCase;
    protected $criteria;
    protected $alternatives;
    protected $matrix; // Matriks keputusan
    protected $normalizedMatrix; // Matriks ternormalisasi
    protected $weightedNormalizedMatrix; // Matriks ternormalisasi berbobot

    public function __construct(DecisionCase $decisionCase)
    {
        $this->decisionCase = $decisionCase->load('criteria', 'alternatives.alternativeCriteriaValues');
        $this->criteria = $this->decisionCase->criteria;
        $this->alternatives = $this->decisionCase->alternatives;
        $this->buildDecisionMatrix();
    }

    /**
     * Membangun matriks keputusan awal dari data alternatif dan kriteria.
     * Baris: Alternatif
     * Kolom: Kriteria
     */
    protected function buildDecisionMatrix()
    {
        $this->matrix = [];
        foreach ($this->alternatives as $altIndex => $alternative) {
            foreach ($this->criteria as $critIndex => $criterion) {
                $value = $alternative->alternativeCriteriaValues
                                     ->where('criterion_id', $criterion->id)
                                     ->first();
                $this->matrix[$altIndex][$critIndex] = $value ? (double)$value->value : 0.0;
            }
        }
    }

    /**
     * Langkah 1: Normalisasi Matriks Keputusan (V).
     * Metode Vektor Normalisasi
     */
    protected function normalizeDecisionMatrix()
    {
        $this->normalizedMatrix = [];
        $denominator = []; // Penyebut untuk setiap kolom (kriteria)

        // Hitung akar kuadrat dari jumlah kuadrat setiap kolom
        foreach ($this->criteria as $critIndex => $criterion) {
            $sumOfSquares = 0;
            foreach ($this->alternatives as $altIndex => $alternative) {
                $sumOfSquares += pow($this->matrix[$altIndex][$critIndex], 2);
            }
            $denominator[$critIndex] = sqrt($sumOfSquares);
        }

        // Lakukan normalisasi
        foreach ($this->alternatives as $altIndex => $alternative) {
            foreach ($this->criteria as $critIndex => $criterion) {
                if ($denominator[$critIndex] == 0) {
                    $this->normalizedMatrix[$altIndex][$critIndex] = 0; // Hindari pembagian nol
                } else {
                    $this->normalizedMatrix[$altIndex][$critIndex] = $this->matrix[$altIndex][$critIndex] / $denominator[$critIndex];
                }
            }
        }
    }

    /**
     * Langkah 2: Matriks Ternormalisasi Berbobot (Y).
     * Y = V * W
     */
    protected function calculateWeightedNormalizedMatrix()
    {
        $this->weightedNormalizedMatrix = [];
        foreach ($this->alternatives as $altIndex => $alternative) {
            foreach ($this->criteria as $critIndex => $criterion) {
                $weight = (double)$criterion->weight;
                $this->weightedNormalizedMatrix[$altIndex][$critIndex] = $this->normalizedMatrix[$altIndex][$critIndex] * $weight;
            }
        }
    }

    /**
     * Langkah 3: Matriks Konkordansi (C).
     * Cij = {h | h adalah kriteria dimana Vi(h) >= Vj(h)}
     */
    protected function calculateConcordanceSet()
    {
        $concordanceSet = [];
        $numAlternatives = count($this->alternatives);
        $numCriteria = count($this->criteria);

        for ($i = 0; $i < $numAlternatives; $i++) {
            for ($j = 0; $j < $numAlternatives; $j++) {
                if ($i == $j) {
                    $concordanceSet[$i][$j] = [];
                    continue;
                }

                $cSet = [];
                foreach ($this->criteria as $k => $criterion) {
                    // Ambil nilai dari matriks ternormalisasi berbobot
                    $yi = $this->weightedNormalizedMatrix[$i][$k];
                    $yj = $this->weightedNormalizedMatrix[$j][$k];

                    if ($criterion->type === 'benefit') {
                        if ($yi >= $yj) {
                            $cSet[] = $k; // Simpan indeks kriteria
                        }
                    } else { // type === 'cost'
                        if ($yi <= $yj) { // Untuk cost, nilai yang lebih kecil lebih baik
                            $cSet[] = $k; // Simpan indeks kriteria
                        }
                    }
                }
                $concordanceSet[$i][$j] = $cSet;
            }
        }
        return $concordanceSet;
    }

    /**
     * Langkah 4: Matriks Diskordansi (D).
     * Dij = {h | h adalah kriteria dimana Vi(h) < Vj(h)}
     */
    protected function calculateDiscordanceSet()
    {
        $discordanceSet = [];
        $numAlternatives = count($this->alternatives);
        $numCriteria = count($this->criteria);

        for ($i = 0; $i < $numAlternatives; $i++) {
            for ($j = 0; $j < $numAlternatives; $j++) {
                if ($i == $j) {
                    $discordanceSet[$i][$j] = [];
                    continue;
                }

                $dSet = [];
                foreach ($this->criteria as $k => $criterion) {
                    $yi = $this->weightedNormalizedMatrix[$i][$k];
                    $yj = $this->weightedNormalizedMatrix[$j][$k];

                    if ($criterion->type === 'benefit') {
                        if ($yi < $yj) {
                            $dSet[] = $k;
                        }
                    } else { // type === 'cost'
                        if ($yi > $yj) { // Untuk cost, nilai yang lebih besar lebih buruk
                            $dSet[] = $k;
                        }
                    }
                }
                $discordanceSet[$i][$j] = $dSet;
            }
        }
        return $discordanceSet;
    }

    /**
     * Langkah 5: Matriks Indeks Konkordansi (C).
     * Cij = (jumlah bobot kriteria di Cij) / (jumlah semua bobot kriteria)
     */
    protected function calculateConcordanceMatrix(array $concordanceSet)
    {
        $concordanceMatrix = [];
        $totalWeight = $this->criteria->sum('weight');
        $numAlternatives = count($this->alternatives);

        if ($totalWeight == 0) { // Hindari pembagian nol jika semua bobot 0
            return array_fill(0, $numAlternatives, array_fill(0, $numAlternatives, 0.0));
        }

        for ($i = 0; $i < $numAlternatives; $i++) {
            for ($j = 0; $j < $numAlternatives; $j++) {
                if ($i == $j) {
                    $concordanceMatrix[$i][$j] = 0.0;
                    continue;
                }

                $sumConcordanceWeight = 0;
                foreach ($concordanceSet[$i][$j] as $critIndex) {
                    $sumConcordanceWeight += (double)$this->criteria[$critIndex]->weight;
                }
                $concordanceMatrix[$i][$j] = $sumConcordanceWeight / $totalWeight;
            }
        }
        return $concordanceMatrix;
    }

    /**
     * Langkah 6: Matriks Indeks Diskordansi (D).
     * Dij = (Max |yj(h) - yi(h)|) / (Max |yk(h) - yl(h)|)
     * h adalah kriteria di Dij
     * yk(h), yl(h) adalah selisih terbesar dari semua alternatif pada kriteria h
     */
    protected function calculateDiscordanceMatrix(array $discordanceSet)
    {
        $discordanceMatrix = [];
        $numAlternatives = count($this->alternatives);
        $numCriteria = count($this->criteria);

        // Cari nilai selisih absolut terbesar dari semua kriteria untuk normalisasi
        $maxDiffOverall = 0;
        foreach ($this->criteria as $k => $criterion) {
            $minVal = INF;
            $maxVal = -INF;
            foreach ($this->alternatives as $altIndex => $alternative) {
                $val = $this->weightedNormalizedMatrix[$altIndex][$k];
                if ($val < $minVal) $minVal = $val;
                if ($val > $maxVal) $maxVal = $val;
            }
            $maxDiffOverall = max($maxDiffOverall, abs($maxVal - $minVal));
        }

        if ($maxDiffOverall == 0) { // Hindari pembagian nol
            return array_fill(0, $numAlternatives, array_fill(0, $numAlternatives, 0.0));
        }

        for ($i = 0; $i < $numAlternatives; $i++) {
            for ($j = 0; $j < $numAlternatives; $j++) {
                if ($i == $j) {
                    $discordanceMatrix[$i][$j] = 0.0;
                    continue;
                }

                // Cari selisih absolut terbesar untuk kriteria di discordance set
                $maxDiffInDiscordanceSet = 0;
                foreach ($discordanceSet[$i][$j] as $critIndex) {
                    $yi = $this->weightedNormalizedMatrix[$i][$critIndex];
                    $yj = $this->weightedNormalizedMatrix[$j][$critIndex];
                    $maxDiffInDiscordanceSet = max($maxDiffInDiscordanceSet, abs($yj - $yi));
                }

                $discordanceMatrix[$i][$j] = $maxDiffInDiscordanceSet / $maxDiffOverall;
            }
        }
        return $discordanceMatrix;
    }

    /**
     * Langkah 7: Matriks Dominansi Konkordansi (F_C).
     * F_Cij = 1 jika Cij >= C_Threshold, 0 jika tidak
     */
    protected function calculateConcordanceDominanceMatrix(array $concordanceMatrix, $concordanceThreshold)
    {
        $dominanceMatrix = [];
        $numAlternatives = count($this->alternatives);

        for ($i = 0; $i < $numAlternatives; $i++) {
            for ($j = 0; $j < $numAlternatives; $j++) {
                if ($i == $j) {
                    $dominanceMatrix[$i][$j] = 0;
                    continue;
                }
                $dominanceMatrix[$i][$j] = ($concordanceMatrix[$i][$j] >= $concordanceThreshold) ? 1 : 0;
            }
        }
        return $dominanceMatrix;
    }

    /**
     * Langkah 8: Matriks Dominansi Diskordansi (F_D).
     * F_Dij = 1 jika Dij <= D_Threshold, 0 jika tidak
     */
    protected function calculateDiscordanceDominanceMatrix(array $discordanceMatrix, $discordanceThreshold)
    {
        $dominanceMatrix = [];
        $numAlternatives = count($this->alternatives);

        for ($i = 0; $i < $numAlternatives; $i++) {
            for ($j = 0; $j < $numAlternatives; $j++) {
                if ($i == $j) {
                    $dominanceMatrix[$i][$j] = 0;
                    continue;
                }
                $dominanceMatrix[$i][$j] = ($discordanceMatrix[$i][$j] <= $discordanceThreshold) ? 1 : 0;
            }
        }
        return $dominanceMatrix;
    }

    /**
     * Langkah 9: Matriks Agregat Dominansi (E).
     * Eij = F_Cij * F_Dij
     */
    protected function calculateAggregateDominanceMatrix(array $concordanceDominanceMatrix, array $discordanceDominanceMatrix)
    {
        $aggregateDominanceMatrix = [];
        $numAlternatives = count($this->alternatives);

        for ($i = 0; $i < $numAlternatives; $i++) {
            for ($j = 0; $j < $numAlternatives; $j++) {
                $aggregateDominanceMatrix[$i][$j] = $concordanceDominanceMatrix[$i][$j] * $discordanceDominanceMatrix[$i][$j];
            }
        }
        return $aggregateDominanceMatrix;
    }

    /**
     * Langkah 10: Perangkingan Alternatif (Opsional, berdasarkan Aggregate Dominance Matrix).
     * Hitung aliran positif dan negatif, atau cukup hitung jumlah dominasi.
     */
    protected function rankAlternatives(array $aggregateDominanceMatrix)
    {
        $numAlternatives = count($this->alternatives);
        $rankings = [];

        // Hitung total dominasi keluar (outranking) untuk setiap alternatif
        foreach ($this->alternatives as $i => $alternative) {
            $outrankingScore = 0;
            for ($j = 0; $j < $numAlternatives; $j++) {
                if ($i != $j) {
                    $outrankingScore += $aggregateDominanceMatrix[$i][$j];
                }
            }
            $rankings[$alternative->id] = [
                'alternative' => $alternative->name,
                'score' => $outrankingScore,
                'rank' => 0 // Akan diisi setelah diurutkan
            ];
        }

        // Urutkan berdasarkan skor (dari yang tertinggi ke terendah)
        uasort($rankings, function($a, $b) {
            if ($a['score'] == $b['score']) {
                return 0;
            }
            return ($a['score'] > $b['score']) ? -1 : 1;
        });

        // Berikan peringkat
        $currentRank = 1;
        $prevScore = null;
        foreach ($rankings as $id => &$ranking) {
            if ($prevScore !== null && $ranking['score'] < $prevScore) {
                $currentRank++;
            }
            $ranking['rank'] = $currentRank;
            $prevScore = $ranking['score'];
        }

        return $rankings;
    }

    /**
     * Metode utama untuk menjalankan seluruh perhitungan ELECTRE.
     * @param float $concordanceThreshold Ambang batas konkordansi (misal: 0.5, 0.6)
     * @param float $discordanceThreshold Ambang batas diskordansi (misal: 0.2, 0.3)
     * @return array Hasil perhitungan ELECTRE
     */
    public function calculateElectre(float $concordanceThreshold = 0.5, float $discordanceThreshold = 0.5)
    {
        // Pastikan ada kriteria dan alternatif
        if ($this->criteria->isEmpty() || $this->alternatives->isEmpty()) {
            return [
                'error' => 'Tidak ada kriteria atau alternatif yang cukup untuk melakukan perhitungan ELECTRE.',
                'rankings' => [],
                'matrices' => []
            ];
        }

        $this->normalizeDecisionMatrix();
        $this->calculateWeightedNormalizedMatrix();

        $concordanceSet = $this->calculateConcordanceSet();
        $discordanceSet = $this->calculateDiscordanceSet();

        $concordanceMatrix = $this->calculateConcordanceMatrix($concordanceSet);
        $discordanceMatrix = $this->calculateDiscordanceMatrix($discordanceSet);

        $concordanceDominanceMatrix = $this->calculateConcordanceDominanceMatrix($concordanceMatrix, $concordanceThreshold);
        $discordanceDominanceMatrix = $this->calculateDiscordanceDominanceMatrix($discordanceMatrix, $discordanceThreshold);

        $aggregateDominanceMatrix = $this->calculateAggregateDominanceMatrix($concordanceDominanceMatrix, $discordanceDominanceMatrix);

        $rankings = $this->rankAlternatives($aggregateDominanceMatrix);

        // Kembalikan semua hasil yang relevan
        return [
            'rankings' => $rankings,
            'matrices' => [
                'decision_matrix' => $this->matrix,
                'normalized_matrix' => $this->normalizedMatrix,
                'weighted_normalized_matrix' => $this->weightedNormalizedMatrix,
                'concordance_set' => $concordanceSet,
                'discordance_set' => $discordanceSet,
                'concordance_matrix' => $concordanceMatrix,
                'discordance_matrix' => $discordanceMatrix,
                'concordance_dominance_matrix' => $concordanceDominanceMatrix,
                'discordance_dominance_matrix' => $discordanceDominanceMatrix,
                'aggregate_dominance_matrix' => $aggregateDominanceMatrix,
            ],
            'concordance_threshold' => $concordanceThreshold,
            'discordance_threshold' => $discordanceThreshold
        ];
    }
}