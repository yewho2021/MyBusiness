<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RefIndustrySeeder extends Seeder
{
    public function run(): void
    {
        $industries = [
            'Agriculture & Plantation' => [
                'Crop Production', 'Livestock & Poultry', 'Fisheries & Aquaculture',
                'Palm Oil & Rubber', 'Forestry & Logging',
            ],
            'Manufacturing' => [
                'Food & Beverage Processing', 'Textile & Apparel', 'Electronics & Electrical',
                'Automotive & Parts', 'Chemical & Pharmaceutical', 'Furniture & Wood Products',
                'Plastic & Rubber Products', 'Metal & Machinery',
            ],
            'Construction & Property' => [
                'Residential Development', 'Commercial Development', 'Infrastructure & Civil Engineering',
                'Interior Design & Renovation', 'Building Materials & Supply',
            ],
            'Wholesale & Retail Trade' => [
                'General Wholesale', 'FMCG & Consumer Goods', 'Electronics & IT Products',
                'Fashion & Lifestyle', 'Automotive Parts & Accessories', 'Health & Beauty',
                'Home & Living', 'E-Commerce & Online Retail',
            ],
            'Food & Beverage' => [
                'Restaurant & Café', 'Catering & Events', 'Food Manufacturing',
                'Bakery & Confectionery', 'Franchise & Chain',
            ],
            'Technology & IT' => [
                'Software Development', 'IT Services & Consulting', 'Web & App Development',
                'Cybersecurity', 'Cloud & Infrastructure', 'AI & Data Analytics',
                'Telecommunications',
            ],
            'Finance & Insurance' => [
                'Banking & Financial Services', 'Insurance & Takaful', 'Investment & Securities',
                'Fintech & Digital Payments', 'Accounting & Bookkeeping',
            ],
            'Healthcare & Wellness' => [
                'Hospital & Clinic', 'Pharmacy & Medical Supply', 'Traditional & Alternative Medicine',
                'Wellness & Spa', 'Health Supplements',
            ],
            'Education & Training' => [
                'School & Tuition Centre', 'Higher Education', 'Vocational & Skills Training',
                'Online Learning & EdTech', 'Language & Enrichment',
            ],
            'Professional Services' => [
                'Legal Services', 'Management Consulting', 'HR & Recruitment',
                'Marketing & Advertising', 'Architecture & Engineering', 'Translation & Interpretation',
            ],
            'Logistics & Transportation' => [
                'Freight & Shipping', 'Courier & Last-Mile Delivery', 'Warehousing & Storage',
                'Fleet Management', 'Ride-Hailing & Transport',
            ],
            'Tourism & Hospitality' => [
                'Hotel & Resort', 'Travel Agency & Tour Operator', 'Event Management',
                'Theme Park & Attraction',
            ],
            'Media & Creative' => [
                'Advertising & Design', 'Film & Video Production', 'Photography',
                'Publishing & Printing', 'Social Media & Influencer',
            ],
            'Energy & Utilities' => [
                'Oil & Gas', 'Renewable Energy (Solar, Wind)', 'Water & Waste Management',
                'Power Generation & Distribution',
            ],
            'Non-Profit & Social Enterprise' => [
                'Charity & Foundation', 'Religious Organisation', 'Community Development',
                'Environmental & Sustainability',
            ],
        ];

        $sortOrder = 1;
        foreach ($industries as $industryName => $subcategories) {
            $industryId = DB::table('tbl_ref_industry')->updateOrInsert(
                ['name' => $industryName],
                ['name' => $industryName, 'sort_order' => $sortOrder, 'status' => 'active']
            );

            $industry = DB::table('tbl_ref_industry')->where('name', $industryName)->first();

            $subOrder = 1;
            foreach ($subcategories as $subName) {
                DB::table('tbl_ref_industry_subcategory')->updateOrInsert(
                    ['industry_id' => $industry->id, 'name' => $subName],
                    ['industry_id' => $industry->id, 'name' => $subName, 'sort_order' => $subOrder, 'status' => 'active']
                );
                $subOrder++;
            }
            $sortOrder++;
        }
    }
}
