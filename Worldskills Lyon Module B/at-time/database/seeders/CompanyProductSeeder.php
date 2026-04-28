<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CompanyProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = $this->seedCompanies();
        $this->seedProducts($companies);
    }

    private function seedCompanies()
    {
        $rows = $this->readCsv(storage_path('app/private/import_data/companies.csv'));
        $companies = collect();

        foreach ($rows as $row) {
            $companies->push(
                Company::updateOrCreate(
                    ['email' => $row['Company Email Address']],
                    [
                        'name' => $row['Company Name'],
                        'address' => $row['Company Address'],
                        'telephone' => $row['Company Telephone Number'],
                        'email' => $row['Company Email Address'],
                        'owner_name' => $row['Owner Name'],
                        'owner_mobile' => $row['Owner Mobile Number'],
                        'owner_email' => $row['Owner Email Address'],
                        'contact_name' => $row['Contact Name'],
                        'contact_mobile' => $row['Contact Mobile Number'],
                        'contact_email' => $row['Contact Email Address'],
                        'is_deactivated' => false,
                    ]
                )
            );
        }

        return $companies;
    }

    private function seedProducts($companies): void
    {
        $rows = $this->readCsv(storage_path('app/private/import_data/products.csv'));

        $imageList = [
            '1.jpg',
            '2.jpg',
            '3.jpg',
            '4.jpg',
            '5.jpg',
            '6.jpg',
            '7.jpg',
            '8.jpg',
        ];

        $companyCount = $companies->count();
        $rowIndex = 0;

        foreach ($rows as $row) {
            if ($companyCount === 0) {
                break;
            }

            $company = $companies[$rowIndex % $companyCount];
            $imageName = $imageList[$rowIndex % count($imageList)];
            $imagePath = 'product_images/'.$imageName;

            Product::updateOrCreate(
                ['gtin' => $row['GTIN']],
                [
                    'company_id' => $company->id,
                    'gtin' => $row['GTIN'],
                    'name_en' => $row['Name'],
                    'name_fr' => $row['Name in French'],
                    'description_en' => $row['Description'],
                    'description_fr' => $row['Description in French'],
                    'brand' => $row['Brand Name'],
                    'country_of_origin' => $row['Country of Origin'],
                    'gross_weight' => (float) $row['Gross Weight (with packaging)'],
                    'net_weight' => (float) $row['Net Content Weight'],
                    'weight_unit' => $row['Weight Unit'],
                    'image_path' => $imagePath,
                    'is_hidden' => false,
                ]
            );

            $rowIndex++;
        }
    }

    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return [];
        }

        $header = fgetcsv($handle);
        $rows = [];

        while (($line = fgetcsv($handle)) !== false) {
            $rows[] = array_combine($header, $line);
        }

        fclose($handle);

        return $rows;
    }
}
