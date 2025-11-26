<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    private $products = [];

    public function __construct()
    {
        $this->generateMockProducts();
    }

    private function generateMockProducts()
    {
        $productNames = [
            'Laptop', 'Mouse', 'Keyboard', 'Monitor', 'Webcam', 'Headphones', 
            'Tablet', 'Smartphone', 'Printer', 'Scanner', 'Router', 'Hard Drive',
            'SSD', 'Memory Card', 'USB Cable', 'Charger', 'Battery', 'Adapter',
            'Dock Station', 'Projector', 'Speaker', 'Microphone', 'Camera', 'Lens',
            'Tripod', 'Flash Drive', 'External Battery', 'Smart Watch', 'Fitness Tracker'
        ];

        for ($i = 1; $i <= 100; $i++) {
            $this->products[] = [
                'id' => $i,
                'name' => $productNames[array_rand($productNames)] . ' ' . $i,
                'currentInventory' => rand(0, 100),
                'averageSalesPerWeek' => rand(5, 50),
                'daysToReplenish' => rand(1, 21)
            ];
        }
    }

    public function index()
    {
        return response()->json([
            'products' => $this->products,
            'total' => count($this->products)
        ]);
    }

    public function getReorderSuggestions(Request $request)
    {
        $suggestions = [];

        foreach ($this->products as $product) {
            $weeksCoverage = $product['currentInventory'] / $product['averageSalesPerWeek'];
            $weeksToReplenish = $product['daysToReplenish'] / 7;
            $safetyStockWeeks = 1;
            
            $needsReorder = $weeksCoverage < ($weeksToReplenish + $safetyStockWeeks);
            
            if ($needsReorder) {
                $suggestions[] = [
                    ...$product,
                    'weeksCoverage' => round($weeksCoverage, 2),
                    'reorderQuantity' => $this->calculateReorderQuantity($product),
                    'urgency' => $this->calculateUrgency($weeksCoverage)
                ];
            }
        }

        usort($suggestions, function($a, $b) {
            return $a['weeksCoverage'] <=> $b['weeksCoverage'];
        });

        return response()->json([
            'suggestions' => $suggestions,
            'total' => count($suggestions)
        ]);
    }

    private function calculateReorderQuantity($product)
    {
        $leadTimeWeeks = $product['daysToReplenish'] / 7;
        $safetyStock = $product['averageSalesPerWeek'];
        return ceil($product['averageSalesPerWeek'] * $leadTimeWeeks + $safetyStock);
    }

    private function calculateUrgency($weeksCoverage)
    {
        if ($weeksCoverage < 1) return 'critical';
        if ($weeksCoverage < 2) return 'high';
        return 'medium';
    }
}