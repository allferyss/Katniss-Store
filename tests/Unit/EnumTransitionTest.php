<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Promotion;
use PHPUnit\Framework\TestCase;

class EnumTransitionTest extends TestCase
{
    public function test_order_status_transitions(): void
    {
        $this->assertTrue(OrderStatus::Pending->canTransitionTo(OrderStatus::Processing));
        $this->assertTrue(OrderStatus::Processing->canTransitionTo(OrderStatus::Completed));
        $this->assertFalse(OrderStatus::Pending->canTransitionTo(OrderStatus::Completed));
        $this->assertFalse(OrderStatus::Completed->canTransitionTo(OrderStatus::Pending));
        $this->assertTrue(OrderStatus::Completed->isFinal());
        $this->assertTrue(OrderStatus::Cancelled->isFinal());
    }

    public function test_payment_status_transitions(): void
    {
        $this->assertTrue(PaymentStatus::Pending->canTransitionTo(PaymentStatus::Paid));
        $this->assertTrue(PaymentStatus::Paid->canTransitionTo(PaymentStatus::Refunded));
        $this->assertFalse(PaymentStatus::Paid->canTransitionTo(PaymentStatus::Pending));
        $this->assertFalse(PaymentStatus::Refunded->canTransitionTo(PaymentStatus::Paid));
    }
}
