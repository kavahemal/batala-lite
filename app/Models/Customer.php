<?php

namespace App\Models;

use App\Traits\Filterable;
use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use Filterable, HasFactory, HasUserTracking, SoftDeletes;

    protected $guarded = [];

    /**
     * Names in English only: letters, digits, space and . - ' & ("R.K. Patel", "Patel & Sons", "Hotel 24 Carat")
     */
    public const NAME_REGEX = "/^[A-Za-z0-9 .\\-'&]+$/";

    public const NAME_MESSAGE = "Name must be in English: letters, digits, space and . - ' & only.";

    /**
     * Digits only, a leading 91 (12 digits) / 0 (11 digits) dropped - same as the customer form JS
     */
    public static function cleanMobile(string $mobile): string
    {
        $digits = preg_replace('/\D/', '', $mobile);
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        }
        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        return $digits;
    }

    protected static function booted(): void
    {
        static::creating(function (Customer $customer) {
            $customer->customer_no = static::generateCustomerNo();
        });
    }

    protected static function generateCustomerNo(): string
    {
        $prefix = 'CUS';
        // Include soft-deleted rows, which still hold their unique number
        $last = static::withTrashed()->latest('id')->value('customer_no');

        $nextNumber = 1;
        if ($last) {
            $lastNumber = (int) substr($last, strlen($prefix));
            $nextNumber = $lastNumber + 1;
        }

        $sequence = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return $prefix.$sequence;
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Other customers with this mobile number (a mobile belongs to one customer only; deleted customers don't count)
     */
    public function scopeSameMobile($query, string $mobile, ?int $exceptId = null)
    {
        return $query->where('mobile_number', $mobile)
            ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
            ->orderBy('customer_no');
    }

    /**
     * Validation rule: mobile not used by another customer, the error names who has it
     */
    public static function uniqueMobileRule(?int $exceptId = null): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($exceptId) {
            if ($other = static::sameMobile((string) $value, $exceptId)->first()) {
                $fail("This mobile is already used by {$other->name} ({$other->customer_no}).");
            }
        };
    }
}
