<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Car extends Model
{
    use HasFactory;

    public static $round = 10_000;

    protected $guarded = [];

    protected $casts = [
        'provider_updated_at' => 'datetime'
    ];

    public static function boot()
    {
        parent::boot();

        static::updating(function (Car $car) {

            if ($car->price && $car->isDirty('price')) {

                $oldPrice = $car->getOriginal('price');

                CarPrice::create([
                    'car_id' => $car->id,
                    'price' => $car->price,
                    'old_price' => $oldPrice,
                    'diff' => $car->price - $oldPrice,
                    'provider_updated_at' => $car->provider_updated_at,
                    'created_at' => now()
                ]);
            }

        });
    }

    public static function disable(string $provider, string $brand, string $model): void
    {
        Car::query()
            ->where('provider', $provider)
            ->where([
                'brand' => $brand,
                'model' => $model,
                'active' => true
            ])
            ->update([
                'active' => false
            ]);
    }

    public static function search(Request $request): array
    {
        $query = self::query();

        $query->select(
            'brand',
            'model',
            'version',
            'year',
            //'price',
            //'odometer',
        );

        $price = 'ceil(price::decimal / 10000) * 10000';
        $odometer = 'ceil(odometer::decimal / 10000) * 10000';

        // Para carros da mesma versão/ano manter apenas o de menor odometro
        $query->addSelect(
            DB::raw("rank() over(partition by brand, model, version, year, ($odometer) order by ($price) asc)"),
            DB::raw("$price as price"),
            DB::raw("$odometer as odometer"),
        );

        $query->whereNotNull('odometer');

        $query->where('price', '>', 0);

        $query->whereRaw('active is true');

        if ($request->price_min) {
            $query->where('price', '>=', $request->price_min * 1000);
        }

        if ($request->price_max) {
            $query->where('price', '<=', $request->price_max * 1000);
        }

        if ($request->year_min) {
            $query->where('year', '>=', $request->year_min);
        }

        if ($request->year_max) {
            $query->where('year', '<=', $request->year_max);
        }

        if ($request->odometer_min) {
            $query->where('odometer', '>=', $request->odometer_min * 1000);
        }

        if ($request->states) {
            $query->whereIn('state', $request->states);
        }

        if ($request->cities) {

            $query->where(function ($query) use ($request) {

                foreach ($request->cities as $text) {

                    list($city, $state) = explode('/', $text);

                    $query->orWhere(function ($q) use ($city, $state) {
                        $q->where('city', $city);
                        $q->where('state', $state);
                    });
                }

            });
        }

        if ($request->models) {

            $query->where(function ($query) use ($request) {

                foreach ($request->models as $text) {

                    list($brand, $model) = explode(' ', $text);

                    $query->orWhere(function ($q) use ($brand, $model) {
                        $q->where('brand', $brand);
                        $q->where('model', $model);
                    });
                }

            });
        }

        $query->where('odometer', '<=', $request->get('odometer_max', 300) * 1000);

        if ($request->state) {
            $query->where('state', $request->state);
        }

        $query->groupBy(
            'brand',
            'model',
            'version',
            'year',
            DB::raw($price),
            DB::raw($odometer)
        );

        $query->orderBy('brand')
            ->orderBy('model');

        $innerSql = vsprintf(
            str_replace('?', '%s', $query->toSql()),
            array_map(function ($binding) {
                $binding = addslashes($binding);
                return is_numeric($binding) ? $binding : "'{$binding}'";
            }, $query->getBindings())
        );

        $sql = "
        select *
        from (
            select
                *,
                rank() over(partition by brand, model, version, price, odometer order by year desc) as rank2
            from (
                $innerSql
            ) q
            where q.rank = 1
        ) q2
        where q2.rank2 = 1";

        $data = DB::select($sql);

        return $data;
    }
}
