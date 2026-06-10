<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;

class ClassificationController extends Controller
{
    public function index()
    {
        $totalTraining = Mahasiswa::count();

        return view('klasifikasi', compact('totalTraining'));
    }

    public function predict(Request $request)
    {
        $request->validate([
            'algoritma' => 'required',
            'ipk' => 'required|numeric',
            'kehadiran' => 'required|numeric',
            'sks_lulus' => 'required|numeric',
            'status_kerja' => 'required'
        ]);

        if ($request->algoritma == 'knn') {
            return $this->predictKNN($request);
        } elseif ($request->algoritma == 'decision_tree') {
            return $this->predictDecisionTree($request);
        } else {
            return $this->predictNaiveBayes($request);
        }
    }

    private function predictKNN(Request $request)
    {
        $allData = Mahasiswa::all();
        $total = $allData->count();

        if ($total == 0) {
            return redirect()->back()
                ->with('error', 'Data training tidak ditemukan.');
        }

        // Menentukan nilai K
        $k = 5;
        if ($k > $total) {
            $k = $total;
        }

        $distances = [];

        // Data testing dari input
        $testIpk = (float) $request->ipk;
        $testKehadiran = (float) $request->kehadiran;
        $testSks = (float) $request->sks_lulus;
        $testKerja = $request->status_kerja == 'Ya' ? 1 : 0;

        foreach ($allData as $data) {
            $trainIpk = (float) $data->ipk;
            $trainKehadiran = (float) $data->kehadiran;
            $trainSks = (float) $data->sks_lulus;
            $trainKerja = $data->status_kerja == 'Ya' ? 1 : 0;

            // Perhitungan Jarak Euclidean (Euclidean Distance)
            $distance = sqrt(
                pow($testIpk - $trainIpk, 2) +
                pow($testKehadiran - $trainKehadiran, 2) +
                pow($testSks - $trainSks, 2) +
                pow($testKerja - $trainKerja, 2)
            );

            $distances[] = [
                'distance' => $distance,
                'tepat_waktu' => $data->tepat_waktu
            ];
        }

        // Mengurutkan array berdasarkan jarak (ascending)
        usort($distances, function($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

        // Mengambil K tetangga terdekat
        $nearestNeighbors = array_slice($distances, 0, $k);

        // Voting kelas terbanyak
        $voteYa = 0;
        $voteTidak = 0;

        foreach ($nearestNeighbors as $neighbor) {
            if ($neighbor['tepat_waktu'] == 'Ya') {
                $voteYa++;
            } else {
                $voteTidak++;
            }
        }

        $hasil = $voteYa > $voteTidak ? 'Ya' : 'Tidak';

        return redirect('/')
            ->with('algoritma', 'knn')
            ->with('prediction', $hasil)
            ->with('vote_ya', $voteYa)
            ->with('vote_tidak', $voteTidak);
    }

    private function predictNaiveBayes(Request $request)
    {
        $total = Mahasiswa::count();

        $totalYa = Mahasiswa::where('tepat_waktu', 'Ya')->count();
        $totalTidak = Mahasiswa::where('tepat_waktu', 'Tidak')->count();

        if ($total == 0) {
            return redirect()->back()
                ->with('error', 'Data training tidak ditemukan.');
        }

        $pYa = $totalYa / $total;
        $pTidak = $totalTidak / $total;

        /*
        |--------------------------------------------------------------------------
        | KATEGORISASI DATA TESTING
        |--------------------------------------------------------------------------
        */

        $ipkTinggi = $request->ipk >= 3;
        $hadirTinggi = $request->kehadiran >= 80;
        $sksTinggi = $request->sks_lulus >= 110;

        /*
        |--------------------------------------------------------------------------
        | PROBABILITAS IPK
        |--------------------------------------------------------------------------
        */

        $ipkYa = Mahasiswa::where('tepat_waktu', 'Ya')
            ->where('ipk', $ipkTinggi ? '>=' : '<', 3)
            ->count();

        $ipkTidak = Mahasiswa::where('tepat_waktu', 'Tidak')
            ->where('ipk', $ipkTinggi ? '>=' : '<', 3)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | PROBABILITAS KEHADIRAN
        |--------------------------------------------------------------------------
        */

        $hadirYa = Mahasiswa::where('tepat_waktu', 'Ya')
            ->where('kehadiran', $hadirTinggi ? '>=' : '<', 80)
            ->count();

        $hadirTidak = Mahasiswa::where('tepat_waktu', 'Tidak')
            ->where('kehadiran', $hadirTinggi ? '>=' : '<', 80)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | PROBABILITAS SKS
        |--------------------------------------------------------------------------
        */

        $sksYa = Mahasiswa::where('tepat_waktu', 'Ya')
            ->where('sks_lulus', $sksTinggi ? '>=' : '<', 110)
            ->count();

        $sksTidak = Mahasiswa::where('tepat_waktu', 'Tidak')
            ->where('sks_lulus', $sksTinggi ? '>=' : '<', 110)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | STATUS KERJA
        |--------------------------------------------------------------------------
        */

        $kerjaYa = Mahasiswa::where('tepat_waktu', 'Ya')
            ->where('status_kerja', $request->status_kerja)
            ->count();

        $kerjaTidak = Mahasiswa::where('tepat_waktu', 'Tidak')
            ->where('status_kerja', $request->status_kerja)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | LAPLACE SMOOTHING
        |--------------------------------------------------------------------------
        */

        $pIpkYa = ($ipkYa + 1) / ($totalYa + 2);
        $pIpkTidak = ($ipkTidak + 1) / ($totalTidak + 2);

        $pHadirYa = ($hadirYa + 1) / ($totalYa + 2);
        $pHadirTidak = ($hadirTidak + 1) / ($totalTidak + 2);

        $pSksYa = ($sksYa + 1) / ($totalYa + 2);
        $pSksTidak = ($sksTidak + 1) / ($totalTidak + 2);

        $pKerjaYa = ($kerjaYa + 1) / ($totalYa + 2);
        $pKerjaTidak = ($kerjaTidak + 1) / ($totalTidak + 2);

        /*
        |--------------------------------------------------------------------------
        | NAIVE BAYES
        |--------------------------------------------------------------------------
        */

        $probYa =
            $pYa *
            $pIpkYa *
            $pHadirYa *
            $pSksYa *
            $pKerjaYa;

        $probTidak =
            $pTidak *
            $pIpkTidak *
            $pHadirTidak *
            $pSksTidak *
            $pKerjaTidak;

        $hasil = $probYa > $probTidak
            ? 'Ya'
            : 'Tidak';

        return redirect('/')
            ->with('algoritma', 'naive_bayes')
            ->with('prediction', $hasil)
            ->with('prob_ya', $probYa)
            ->with('prob_tidak', $probTidak);
    }

    private function predictDecisionTree(Request $request)
    {
        $allData = Mahasiswa::all();
        $total = $allData->count();

        if ($total == 0) {
            return redirect()->back()
                ->with('error', 'Data training tidak ditemukan.');
        }

        // 1. Kategorisasi Data Training
        $dataset = [];
        foreach ($allData as $data) {
            $dataset[] = [
                'IPK' => $data->ipk >= 3 ? 'Tinggi' : 'Rendah',
                'Kehadiran' => $data->kehadiran >= 80 ? 'Tinggi' : 'Rendah',
                'SKS' => $data->sks_lulus >= 110 ? 'Tinggi' : 'Rendah',
                'Status Kerja' => $data->status_kerja, // "Ya" / "Tidak"
                'tepat_waktu' => $data->tepat_waktu
            ];
        }

        $attributes = ['IPK', 'Kehadiran', 'SKS', 'Status Kerja'];

        // 2. Bangun Decision Tree
        $tree = $this->buildTree($dataset, $attributes);

        // 3. Siapkan Data Testing
        $testData = [
            'IPK' => $request->ipk >= 3 ? 'Tinggi' : 'Rendah',
            'Kehadiran' => $request->kehadiran >= 80 ? 'Tinggi' : 'Rendah',
            'SKS' => $request->sks_lulus >= 110 ? 'Tinggi' : 'Rendah',
            'Status Kerja' => $request->status_kerja
        ];

        // 4. Telusuri Pohon (Traverse)
        $path = [];
        $hasil = $this->traverseTree($tree, $testData, $path);

        return redirect('/')
            ->with('algoritma', 'decision_tree')
            ->with('prediction', $hasil)
            ->with('path', $path);
    }

    private function calculateEntropy($data)
    {
        if (count($data) == 0) return 0;
        $ya = 0; $tidak = 0;
        foreach ($data as $d) {
            if ($d['tepat_waktu'] == 'Ya') $ya++;
            else $tidak++;
        }
        $pYa = $ya / count($data);
        $pTidak = $tidak / count($data);
        $entropy = 0;
        if ($pYa > 0) $entropy -= $pYa * log($pYa, 2);
        if ($pTidak > 0) $entropy -= $pTidak * log($pTidak, 2);
        return $entropy;
    }

    private function calculateGain($data, $attribute)
    {
        $entropyS = $this->calculateEntropy($data);
        $values = [];
        foreach ($data as $d) {
            $val = $d[$attribute];
            if (!isset($values[$val])) $values[$val] = [];
            $values[$val][] = $d;
        }
        $total = count($data);
        $gain = $entropyS;
        foreach ($values as $val => $subset) {
            $p = count($subset) / $total;
            $gain -= $p * $this->calculateEntropy($subset);
        }
        return $gain;
    }

    private function buildTree($data, $attributes)
    {
        // Kondisi 1: Jika semua data memiliki kelas yang sama
        $allYa = true; $allTidak = true;
        foreach ($data as $d) {
            if ($d['tepat_waktu'] != 'Ya') $allYa = false;
            if ($d['tepat_waktu'] != 'Tidak') $allTidak = false;
        }
        if ($allYa) return 'Ya';
        if ($allTidak) return 'Tidak';

        // Kondisi 2: Jika atribut habis, return kelas terbanyak
        if (count($attributes) == 0) {
            $ya = 0; $tidak = 0;
            foreach ($data as $d) {
                if ($d['tepat_waktu'] == 'Ya') $ya++; else $tidak++;
            }
            return $ya >= $tidak ? 'Ya' : 'Tidak';
        }

        // Cari atribut dengan Gain tertinggi
        $bestAttr = null;
        $bestGain = -1;
        foreach ($attributes as $attr) {
            $gain = $this->calculateGain($data, $attr);
            if ($gain > $bestGain) {
                $bestGain = $gain;
                $bestAttr = $attr;
            }
        }

        // Jika tidak ada gain (misal data seragam semua tapi kelas beda)
        if ($bestGain == 0) {
            $ya = 0; $tidak = 0;
            foreach ($data as $d) {
                if ($d['tepat_waktu'] == 'Ya') $ya++; else $tidak++;
            }
            return $ya >= $tidak ? 'Ya' : 'Tidak';
        }

        $tree = ['attribute' => $bestAttr, 'children' => []];

        // Pisahkan data berdasarkan nilai dari atribut terbaik
        $values = [];
        foreach ($data as $d) {
            $val = $d[$bestAttr];
            if (!isset($values[$val])) $values[$val] = [];
            $values[$val][] = $d;
        }

        // Buang atribut yang sudah digunakan
        $newAttributes = array_values(array_filter($attributes, function ($a) use ($bestAttr) {
            return $a != $bestAttr;
        }));

        // Buat node anak (rekursif)
        foreach ($values as $val => $subset) {
            $tree['children'][$val] = $this->buildTree($subset, $newAttributes);
        }

        return $tree;
    }

    private function traverseTree($tree, $testData, &$path)
    {
        // Jika sudah mencapai daun (Leaf Node)
        if (!is_array($tree)) {
            return $tree;
        }

        $attr = $tree['attribute'];
        $val = $testData[$attr];

        $path[] = "Atribut " . $attr . " = " . $val;

        // Lanjut menelusuri ke bawah sesuai nilai testing
        if (isset($tree['children'][$val])) {
            return $this->traverseTree($tree['children'][$val], $testData, $path);
        } else {
            // Jika nilai ini tidak pernah ada di data training, ambil suara terbanyak keseluruhan
            $totalYa = Mahasiswa::where('tepat_waktu', 'Ya')->count();
            $totalTidak = Mahasiswa::where('tepat_waktu', 'Tidak')->count();
            return $totalYa >= $totalTidak ? 'Ya' : 'Tidak';
        }
    }
}