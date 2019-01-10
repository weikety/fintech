<?php
/**
 * Created by PhpStorm.
 * User: achais
 * Date: 2019/1/9
 * Time: 6:26 PM
 */

namespace Achais\FinTech\Tests;

use Achais\FinTech\Calculator;
use Achais\FinTech\Investment;
use Achais\FinTech\Product;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase
{
    protected $product;

    protected $investment;

    protected function getProduct()
    {
        if (!$this->product) {
            $rate = 8;
            $loanTerm = 24;
            $termType = Product::TERM_TYPE_MONTH;
            $foundDate = Carbon::create('2019-07-08');
            $repayMode = Product::REPAY_MODE_NATURAL_QUARTER;
            $repayDay = 20;
            $repayMonth = 6;
            $advanceInterest = true;

            $product = new Product();
            $product->init($rate, $loanTerm, $repayMode, $foundDate, $termType, $repayDay, $repayMonth, $advanceInterest);

            $this->product = $product;
        }

        return $this->product;
    }

    protected function getInvestment()
    {
        if (!$this->investment) {
            $investDateTime = Carbon::create('2019-07-05 12:00:00');
            $amount = 10000;

            $investment = new Investment($investDateTime, $amount);

            $this->investment = $investment;
        }

        return $this->investment;
    }

    public function testCreateCalculator()
    {
        $product = $this->getProduct();
        $calculator = new Calculator($product);

        $this->assertInstanceOf(Calculator::class, $calculator);
        $this->assertInstanceOf(Product::class, $calculator->getProduct());
    }

    public function testCalcRepayment()
    {
        $product = $this->getProduct();
        $investment = $this->getInvestment();

        $calculator = new Calculator($product);
        $repaymentList = $calculator->getRepaymentList($investment);

        print_r(PHP_EOL);
        print_r('产品成立时间: ' . $product->getFoundDate() . PHP_EOL);
        print_r('产品到期时间: ' . $product->getEndDate() . PHP_EOL);
        print_r('产品实际天数: ' . $product->getLoanTermDays() . PHP_EOL);
        print_r('产品利率: ' . $product->getRate() . '%' . PHP_EOL);
        print_r('产品兑付方式: ' . $product->getRepayModeName() . PHP_EOL);
        print_r('指定兑付月: ' . $product->getRepayMonth() . PHP_EOL);
        print_r('指定兑付日: ' . $product->getRepayDay() . PHP_EOL);

        printf(PHP_EOL);
        printf('认购金额: %s' . PHP_EOL, $investment->getAmount());
        printf('认购时间: %s' . PHP_EOL, $investment->getInvestDateTime()->toDateTimeString());
        printf(PHP_EOL);

        foreach ($repaymentList as $repayment) {
            printf('兑付时间点: %s | 计息天数: %s | 兑付利息: %s | 加息天数: %s | 加息金额: %s | 本金: %s | 总金额: %s' . PHP_EOL,
                $repayment->getRepaymentDate(),
                $repayment->getDays(),
                $repayment->getRepaymentInterest(),
                $repayment->getExtraDays(),
                $repayment->getExtraRepaymentInterest(),
                $repayment->getRepaymentInvestmentAmount(),
                $repayment->getTotalRepaymentAmount()
            );
        }

        $this->assertCount(9, $repaymentList);
    }
}