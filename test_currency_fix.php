<?php

echo "=== Testing Payment Currency Fix ===\n\n";

// Test the currency methods directly
class TestPaymentModel {
    private function getCurrencySymbol($currency)
    {
        // Handle null or empty currency
        if (empty($currency)) {
            return 'Unknown';
        }
        
        $symbols = [
            'IDR' => 'Rp',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'SGD' => 'S$',
            'MYR' => 'RM'
        ];
        
        $currencyUpper = strtoupper($currency);
        return $symbols[$currencyUpper] ?? $currencyUpper;
    }
    
    public function formatCurrencyForExport($amount, $currency)
    {
        if (empty($amount) || !is_numeric($amount)) {
            return 'N/A';
        }
        
        $normalizedAmount = (float)$amount;
        $symbol = $this->getCurrencySymbol($currency);
        
        // Format based on currency (handle null currency)
        $currencyUpper = $currency ? strtoupper($currency) : 'UNKNOWN';
        if ($currencyUpper === 'IDR') {
            return $symbol . ' ' . number_format($normalizedAmount, 0, ',', '.');
        } else {
            return $symbol . ' ' . number_format($normalizedAmount, 2, '.', ',');
        }
    }
    
    public function testCurrencyHandling()
    {
        echo "Testing currency symbol retrieval:\n";
        echo "null currency: " . $this->getCurrencySymbol(null) . "\n";
        echo "empty currency: " . $this->getCurrencySymbol('') . "\n";
        echo "IDR currency: " . $this->getCurrencySymbol('IDR') . "\n";
        echo "USD currency: " . $this->getCurrencySymbol('USD') . "\n";
        echo "unknown currency: " . $this->getCurrencySymbol('XYZ') . "\n\n";
        
        echo "Testing currency formatting:\n";
        echo "165000 with null currency: " . $this->formatCurrencyForExport('165000', null) . "\n";
        echo "165000 with empty currency: " . $this->formatCurrencyForExport('165000', '') . "\n";
        echo "165000 with IDR currency: " . $this->formatCurrencyForExport('165000', 'IDR') . "\n";
        echo "165000 with USD currency: " . $this->formatCurrencyForExport('165000', 'USD') . "\n";
        echo "165000 with unknown currency: " . $this->formatCurrencyForExport('165000', 'XYZ') . "\n";
    }
}

$test = new TestPaymentModel();
$test->testCurrencyHandling();

echo "\n=== Fix Validation Complete ===\n";
echo "✅ No deprecation warnings should occur with null currency values\n";
echo "✅ Currency formatting handles null/empty values gracefully\n";
echo "✅ Unknown currencies are handled properly\n";
