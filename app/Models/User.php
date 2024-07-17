<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'image_url',
        'phonenumber',
        'verify_code',
        'overtimed_at',
        'status',
        'gender',
        'remember_token',
        'date_of_birth',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getJWTIdentifier() {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims() {
        return [];
    }

    public function addresses(): HasMany {
        return $this->hasMany(Address::class);
    }

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function hasPurchasedProduct($productId)
    {
        return $this->orders()->whereHas('orderItems', function ($query) use ($productId) {
            $query->where('product_id', $productId);
        })->exists();
    }

    public function handledOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'staff_id');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(Rate::class);
    }

    public function userDiscounts(): HasMany
    {
        return $this->hasMany(UserDiscount::class);
    }

    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'user_discounts')->whereNull('user_discounts.deleted_at');
    }

    public function scopeSearch($query, $search, $role = null, $status = null)
    {
        if (!is_null($role)) {
            $query->where('role', $role);
        }

        if (!is_null($status)) {
            $query->where('status', $status);
        }

        if ($search === '') {
            return $query;
        }

        $keywords = explode(',', $search);

        $query->where(function ($query) use ($keywords) {
            foreach ($keywords as $keyword) {
                $keywordWithoutAccent = $this->removeAccents(mb_strtolower(trim($keyword)));
                $query->orWhere(function ($query) use ($keywordWithoutAccent) {
                    $query->whereRaw('LOWER(UNACCENT(username)) LIKE ?', ["%$keywordWithoutAccent%"])
                        ->orWhereRaw('unaccent(LOWER(email)) LIKE ?', ["%$keywordWithoutAccent%"])
                        ->orWhereRaw('unaccent(LOWER(phonenumber)) LIKE ?', ["%$keywordWithoutAccent%"]);
                });
            }
        });

        return $query;
    }

    public function scopeSingleUser($query, $id)
    {
        return $query->where('id', $id);
    }
}
