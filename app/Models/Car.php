<?php

namespace App\Models;

use App\Enums\CarProviderEnum;
use App\Jobs\Olx\OlxUpdateJob;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Car extends Model
{
    use HasFactory;

    public static $round = 1;

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

        static::saving(function (Car $car) {

            foreach ($car->attributes as $key => $value) {

                if (is_string($value) && $key != 'provider_id') {
                    $car->$key = mb_strtolower($value);
                }
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

    public static function getChartsData(Request $request): array
    {
        $query = self::search($request);

        $query->select(
            'brand',
            'model',
            'version',
            'year',
        );

        $priceRounded = 'ceil(price::decimal / '.self::$round.') * '.self::$round;
        $odometerRounded = 'ceil(odometer::decimal / '.self::$round.') * '.self::$round;

        // Para carros da mesma versão/ano manter apenas o de menor odometro
        $query->addSelect(
            DB::raw("rank() over(partition by brand, model, version, year, ($priceRounded) order by ($odometerRounded) asc)"),
            DB::raw("$priceRounded as price"),
            DB::raw("$odometerRounded as odometer"),
        );

        $query->groupBy(
            'brand',
            'model',
            'version',
            'year',
            DB::raw($priceRounded),
            DB::raw($odometerRounded)
        );

        $sql = "
        select *
        from (
            select
                *,
                rank() over(partition by brand, model, version, price, odometer order by year desc) as rank2
            from (
                ".self::getEloquentSqlWithBindings($query)."
            ) q
            where q.rank = 1
        ) q2
        where q2.rank2 = 1";

        $data = DB::select($sql);

        return $data;
    }

    public function scopeSearch($query, Request $request): void
    {
        $query->whereRaw('active is true')
            ->whereRaw('banned is false')
            ->where('price', '>', 1300)
            ->where('odometer', '>', 1000);

        $query->where('provider', '=', CarProviderEnum::OLX);

        if (is_numeric($request->year_min)) {
            $query->where('year', '>=', $request->year_min);
        }

        if (is_numeric($request->year_max)) {
            $query->where('year', '<=', $request->year_max);
        }

        if (is_numeric($request->price_min)) {
            $query->where('price', '>=', $request->price_min * 1000);
        }

        if (is_numeric($request->price_max)) {
            $query->where('price', '<=', $request->price_max * 1000);
        }

        if (is_numeric($request->odometer_min)) {
            $query->where('odometer', '>=', $request->odometer_min * 1000);
        }

        if (is_numeric($request->odometer_max)) {
            $query->where('odometer', '<=', $request->odometer_max * 1000);
        }

        $states = $request->get('states', []);
        $cities = $request->get('cities', []);
        $models = $request->get('models', []);

        $query->when(count($states) > 0, function ($query) use ($states) {
            $query->whereIn('state', $states);
        });

        $query->when(count($cities) > 0, function ($query) use ($cities) {

            $query->where(function ($query) use ($cities) {

                foreach ($cities as $text) {

                    /* list($city, $state) = explode('/', $text);

                    $query->orWhere(function ($q) use ($city, $state) {
                        $q->where('city', $city)->where('state', $state);
                    }); */
                }

            });
            
        });

        $query->when(count($models) > 0, function ($query) use ($models) {

            $query->where(function ($query) use ($models) {

                foreach ($models as $text) {

                    list($brand, $model) = explode(' ', $text);

                    $query->orWhere(function ($q) use ($brand, $model) {
                        $q->where('brand', $brand)->whereRaw("model = '$model'");
                    });
                }

            });
        });
    }

    public static function getEloquentSqlWithBindings($query): string
    {
        return vsprintf(
            str_replace('?', '%s', $query->toSql()),
            array_map(function ($binding) {
                $binding = addslashes($binding);
                return is_numeric($binding) ? $binding : "'{$binding}'";
            }, $query->getBindings())
        );
    }
}
