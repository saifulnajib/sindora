<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Support\Str;

class IndonesianPersonData
{
    protected static array $maleFirstNames = [
        'Aditya', 'Agung', 'Ahmad', 'Aldi', 'Alif', 'Andika', 'Angga', 'Anjas', 'Anton', 'Arif',
        'Arya', 'Bagas', 'Bagus', 'Bambang', 'Bayu', 'Bima', 'Bintang', 'Bobby', 'Cahyo', 'Candra',
        'Daffa', 'Danang', 'Dani', 'Denny', 'Dedi', 'Dedy', 'Dewa', 'Dimas', 'Dion', 'Doni',
        'Dwi', 'Eko', 'Fajar', 'Fakhri', 'Farhan', 'Fathir', 'Fauzan', 'Febri', 'Ferdi', 'Galang',
        'Gani', 'Gilang', 'Gunawan', 'Hadi', 'Hafiz', 'Hamzah', 'Haryanto', 'Hendra', 'Hendri', 'Heri',
        'Ilham', 'Imam', 'Indra', 'Iqbal', 'Irfan', 'Iwan', 'Joko', 'Jonathan', 'Julian', 'Kevin',
        'Kurnia', 'Lukman', 'Miftah', 'Mirza', 'Naufal', 'Nur', 'Panji', 'Pratama', 'Raden', 'Rafi',
        'Rahmat', 'Rama', 'Randy', 'Rangga', 'Rendy', 'Reza', 'Rian', 'Rico', 'Ridwan', 'Rifat',
        'Rio', 'Riski', 'Rizal', 'Rizky', 'Ryan', 'Sandy', 'Satria', 'Septian', 'Surya', 'Syahrul',
        'Tegar', 'Taufik', 'Wahyu', 'Wawan', 'Wisnu', 'Yoga', 'Yudha', 'Yusuf', 'Zaki', 'Zulfikar',
        'Akbar', 'Fikri', 'Haidar', 'Zidan', 'Reyhan', 'Arjun', 'Farel', 'Kenzo', 'Bramantyo', 'Danendra',
        'Syahroni', 'Firmansyah', 'Gibran', 'Haris', 'Irsyad', 'Kamal', 'Mahardika', 'Nashir', 'Oki', 'Pandu'
    ];

    protected static array $femaleFirstNames = [
        'Ade', 'Adinda', 'Aisyah', 'Amanda', 'Amelia', 'Ananda', 'Anggraini', 'Anisa', 'Annisa', 'Aprilia',
        'Arini', 'Aulia', 'Ayu', 'Bella', 'Cantika', 'Citra', 'Cut', 'Dara', 'Dea', 'Dewi',
        'Diah', 'Dila', 'Dinda', 'Dwi', 'Elisa', 'Elvira', 'Endang', 'Erina', 'Fani', 'Farah',
        'Fatimah', 'Febiola', 'Fitri', 'Fitria', 'Gita', 'Hana', 'Hanifah', 'Hesti', 'Indah', 'Intan',
        'Irma', 'Jessica', 'Julia', 'Kartika', 'Kiki', 'Laras', 'Lestari', 'Linda', 'Lisa', 'Maya',
        'Mega', 'Melati', 'Melinda', 'Mita', 'Mutia', 'Nabila', 'Nadia', 'Nadira', 'Nanda', 'Natasha',
        'Nia', 'Nina', 'Novia', 'Nur', 'Nurul', 'Putri', 'Rahma', 'Ratna', 'Reni', 'Rina',
        'Rini', 'Risma', 'Riska', 'Safira', 'Salsa', 'Sari', 'Sela', 'Selvi', 'Septi', 'Shafa',
        'Silvi', 'Sintia', 'Siti', 'Sri', 'Suci', 'Syafira', 'Syifa', 'Tiara', 'Tri', 'Ulfa',
        'Vina', 'Winda', 'Wulan', 'Yanti', 'Yolanda', 'Yulia', 'Zahra', 'Kusuma', 'Zalfa', 'Khansa',
        'Azzahra', 'Batari', 'Clara', 'Delfina', 'Eka', 'Fadilla', 'Ghefira', 'Hafidzah', 'Inas', 'Khadijah'
    ];

    protected static array $middleNames = [
        'Bagus', 'Dwi', 'Tri', 'Cahya', 'Kurnia', 'Agung', 'Pratama', 'Surya', 'Fajar', 'Satria',
        'Putra', 'Putri', 'Bintang', 'Wira', 'Dharma', 'Kusuma', 'Ayu', 'Lestari', 'Permana', 'Indah',
        'Rizky', 'Maulana', 'Hidayat', 'Firmansyah', 'Santoso', 'Ramadhan', 'Nugraha', 'Mahardika', 'Purnama', 'Dewi',
        'Septian', 'Oktavian', 'Novian', 'Yudha', 'Bayu', 'Bima', 'Ananda', 'Wijaya', 'Utami', 'Wardani'
    ];

    protected static array $lastNames = [
        'Abidin', 'Afandi', 'Akbar', 'Alamsyah', 'Anwar', 'Arifin', 'Asyari', 'Azhar', 'Bachtiar', 'Basuki',
        'Batubara', 'Budiman', 'Chaniago', 'Daulay', 'Dharmawan', 'Effendi', 'Fauzi', 'Firmansyah', 'Ginting', 'Gunawan',
        'Hakim', 'Halim', 'Hamdani', 'Harahap', 'Haryadi', 'Hasan', 'Hasibuan', 'Hidayat', 'Hidayatullah', 'Hutapea',
        'Irawan', 'Iskandar', 'Ismail', 'Jamil', 'Jaya', 'Kurniawan', 'Kusuma', 'Lestari', 'Lubis', 'Mahardika',
        'Mahendra', 'Mahmud', 'Mansyur', 'Marpaung', 'Maulana', 'Mubaraq', 'Mulyadi', 'Munandar', 'Nasution', 'Nugraha',
        'Nugroho', 'Nurdiansyah', 'Oktaviano', 'Pangestu', 'Pasaribu', 'Permana', 'Prabowo', 'Prakoso', 'Prasetya', 'Prasetyo',
        'Pratama', 'Prayoga', 'Purnomo', 'Putra', 'Putri', 'Ramadhan', 'Rauf', 'Ridwan', 'Ritonga', 'Rohman',
        'Rusdi', 'Rustam', 'Sahputra', 'Salim', 'Sanjaya', 'Santoso', 'Saputra', 'Sari', 'Saragih', 'Setiawan',
        'Sianipar', 'Sihombing', 'Simanjuntak', 'Siregar', 'Sitompul', 'Soleh', 'Subagyo', 'Sudrajat', 'Sugiono', 'Suhartono',
        'Sulaiman', 'Sumanto', 'Supriadi', 'Suryadi', 'Susanto', 'Sutarman', 'Syaifullah', 'Syahputra', 'Syamsudin', 'Tambunan',
        'Tanjung', 'Tarigan', 'Utomo', 'Wahyudi', 'Wibowo', 'Wicaksono', 'Widodo', 'Wijaya', 'Winata', 'Wira',
        'Yuliansyah', 'Yulianto', 'Zulkarnain', 'Kusnadi', 'Sulaeman', 'Pamungkas', 'Wardhana', 'Subekti', 'Hutabarat'
    ];

    protected static array $coachTitles = [
        ', S.Pd.', ', S.Pd.', ', S.Or.', ', S.Or.', ', M.Pd.', ', S.E.', ', S.Kom.', ', S.Si.', '', '', ''
    ];

    protected static array $streets = [
        'Jl. Basuki Rahmat', 'Jl. D.I. Panjaitan', 'Jl. R.H. Fisabilillah', 'Jl. Raja Ali Haji',
        'Jl. Hang Tuah', 'Jl. Brigjen Katamso', 'Jl. Ahmad Yani', 'Jl. Teuku Umar',
        'Jl. Pos', 'Jl. Merdeka', 'Jl. Ketapang', 'Jl. Gudang Minyak', 'Jl. Pantai Impian',
        'Jl. Wiratno', 'Jl. Soekarno Hatta', 'Jl. Ganet', 'Jl. Bandara Km. 12',
        'Jl. Senggarang', 'Jl. Sei Carang', 'Jl. Dompak', 'Jl. Pramuka', 'Jl. Dr. Sutomo',
        'Jl. Sunaryo', 'Jl. Kamboja', 'Jl. Pelantar II', 'Jl. Batu 8 Atas'
    ];

    protected static array $birthPlaces = [
        'Tanjungpinang', 'Tanjungpinang', 'Tanjungpinang', 'Batam', 'Bintan', 'Bintan',
        'Karimun', 'Natuna', 'Lingga', 'Dabo Singkep', 'Pekanbaru', 'Medan', 'Padang', 'Jakarta'
    ];

    protected static array $usedNiks = [];
    protected static array $usedNames = [];

    public static function generatePerson(string $gender, int $minAge, int $maxAge, int $kelurahanId, bool $isCoach = false): array
    {
        $firstNames = $gender === 'L' ? self::$maleFirstNames : self::$femaleFirstNames;
        
        do {
            $first = $firstNames[array_rand($firstNames)];
            $useMiddle = rand(1, 100) <= 45;
            $middle = $useMiddle ? self::$middleNames[array_rand(self::$middleNames)] : '';
            $last = self::$lastNames[array_rand(self::$lastNames)];

            $nameParts = array_filter([$first, $middle, $last]);
            $baseName = implode(' ', $nameParts);
        } while (isset(self::$usedNames[$baseName]));

        self::$usedNames[$baseName] = true;

        $title = $isCoach ? self::$coachTitles[array_rand(self::$coachTitles)] : '';
        $fullName = $baseName . $title;

        // Birth date
        $birthYear = Carbon::now()->year - rand($minAge, $maxAge);
        $birthMonth = rand(1, 12);
        $birthDay = rand(1, 28);
        $birthDate = Carbon::createFromDate($birthYear, $birthMonth, $birthDay);

        // NIK calculation (Tanjungpinang standard)
        // Kecamatan mapping from Kelurahan:
        // 1..5: Bukit Bestari (217204)
        // 6..9: Tanjungpinang Barat (217201)
        // 10..14: Tanjungpinang Timur (217202)
        // 15..18: Tanjungpinang Kota (217203)
        $kecCode = match (true) {
            $kelurahanId >= 1 && $kelurahanId <= 5 => '217204',
            $kelurahanId >= 6 && $kelurahanId <= 9 => '217201',
            $kelurahanId >= 10 && $kelurahanId <= 14 => '217202',
            default => '217203',
        };

        $nikDay = $gender === 'P' ? ($birthDay + 40) : $birthDay;
        $nikDateStr = sprintf('%02d%02d%02d', $nikDay, $birthMonth, $birthYear % 100);

        $serial = 1;
        do {
            $nik = sprintf('%s%s%04d', $kecCode, $nikDateStr, $serial);
            $serial++;
        } while (isset(self::$usedNiks[$nik]));

        self::$usedNiks[$nik] = true;

        $street = self::$streets[array_rand(self::$streets)];
        $houseNum = rand(1, 140);
        $rt = rand(1, 7);
        $rw = rand(1, 5);
        $address = "{$street} No. {$houseNum}, RT 0{$rt}/RW 0{$rw}, Kota Tanjungpinang";

        $phonePrefixes = ['0812', '0813', '0821', '0822', '0852', '0853', '0878', '0896', '0819'];
        $phone = $phonePrefixes[array_rand($phonePrefixes)] . sprintf('%08d', rand(10000000, 99999999));

        $slug = Str::slug($baseName, '.');
        $email = $slug . rand(10, 999) . '@gmail.com';

        $tempatLahir = self::$birthPlaces[array_rand(self::$birthPlaces)];

        return [
            'nama' => $fullName,
            'nik' => $nik,
            'jenis_kelamin' => $gender,
            'tempat_lahir' => $tempatLahir,
            'tanggal_lahir' => $birthDate->format('Y-m-d'),
            'alamat' => $address,
            'no_hp' => $phone,
            'email' => $email,
            'kelurahan_id' => $kelurahanId,
        ];
    }
}
