<?php

namespace App\Http\Controllers\Traits;

trait SystemPriceCalculator
{
    /**
     * Service logic
     *  User always can make request by sending Billing price and System price
     *      if both are 0 :
     *          the Service :: Billing and System prices returns
     *      if the Billing price is not zero but the System price
     *          Billing price = (Sending value - System price)
     *          System price = System price
     *      if the Billing price is zero but the System price is not
     *          Both are = System price
     *      if the both values are not zero
     *          Same as input values
     *
     * How the billing and system prices are calculated
     *      If the is_percentage == true
     *          System price = Requesting Billing price *  Service :: System price /100
     *          Billing price = Requesting Billing price - System price
     */

    /**
     * @param $service
     * @param float $billAmount
     * @param float $systemAmount
     * @return float
     *
     * The BillAmount is the charging amount from the patient
     * The system amount need to be calculated.
     */
    public function calculateSystemPrice($service, float $billAmount = 0, float $systemAmount = 0): float
    {

        if ($service) {
            if ($service->is_percentage) {
                return $billAmount * $service->system_price / 100;
            } else {
                if ($systemAmount == 0) {
                    return $service->system_price;
                }
            }
        }
        return $systemAmount;
    }

    public function getBillPriceAndSystemPrice($service, float $billAmount = 0, float $systemAmount = 0): array
    {
        if (!$service) {
            return [$billAmount, $systemAmount];
        }

        $isPercentage = $service->is_percentage;
        $systemPrice = $service->system_price;
        $billPrice = $service->bill_price;

        if ($billAmount == 0 && $systemAmount == 0) {
            $calculatedSystemAmount = $isPercentage ? $billPrice * $systemPrice / 100 : $systemPrice;
            $calculatedBillAmount = $billPrice;
        } elseif ($systemAmount == 0) {
            $calculatedSystemAmount = $isPercentage ? $billAmount * $systemPrice / 100 : $systemPrice;
            $calculatedBillAmount = $billAmount - $calculatedSystemAmount;
        } else {
            $calculatedSystemAmount = $systemAmount;
            $calculatedBillAmount = $billAmount;
        }

        return [number_format($calculatedBillAmount, 2, thousands_separator: ''), number_format($calculatedSystemAmount, 2, thousands_separator: '')];
    }

}
