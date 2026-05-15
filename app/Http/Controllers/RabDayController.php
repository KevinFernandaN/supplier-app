<?php

namespace App\Http\Controllers;

use App\Models\RabDay;
use App\Models\RabPeriod;
use Illuminate\Http\Request;

class RabDayController extends Controller
{
    public function show(RabPeriod $rabPeriod, RabDay $day)
    {
        $day->load(['menus' => function ($q) {
            $q->with(['menu', 'items.product', 'items.unit', 'items.supplier', 'replacements']);
        }]);

        foreach ($day->menus as $menu) {
            $menu->setRelation('day', $day);
            foreach ($menu->replacements as $rep) {
                $rep->setRelation('day', $day);
            }
        }

        $budget  = $day->budget();
        $rfc     = $day->rfc();
        $surplus = $day->surplus();

        return view('rab-days.show', compact('rabPeriod', 'day', 'budget', 'rfc', 'surplus'));
    }

    public function create(RabPeriod $rabPeriod)
    {
        $usedDates = $rabPeriod->days()
            ->pluck('day_date')
            ->map(fn($d) => $d->toDateString())
            ->values()
            ->toArray();

        return view('rab-days.create', compact('rabPeriod', 'usedDates'));
    }

    public function store(Request $request, RabPeriod $rabPeriod)
    {
        $data = $request->validate([
            'day_date' => [
                'required', 'date',
                'after_or_equal:' . $rabPeriod->start_date->toDateString(),
                'before_or_equal:' . $rabPeriod->end_date->toDateString(),
                // Unique within this period
                'unique:rab_days,day_date,NULL,id,rab_period_id,' . $rabPeriod->id,
            ],
            'pk_count' => 'required|integer|min:0',
            'pb_count' => 'required|integer|min:0',
        ]);

        $data['rab_period_id'] = $rabPeriod->id;

        RabDay::create($data);

        return redirect()->route('rab-periods.show', $rabPeriod)
            ->with('success', 'Day added. Fill in Realisasi once actual field spending is known.');
    }

    public function edit(RabPeriod $rabPeriod, RabDay $day)
    {
        return view('rab-days.edit', compact('rabPeriod', 'day'));
    }

    public function update(Request $request, RabPeriod $rabPeriod, RabDay $day)
    {
        $data = $request->validate([
            'day_date' => [
                'required', 'date',
                'after_or_equal:' . $rabPeriod->start_date->toDateString(),
                'before_or_equal:' . $rabPeriod->end_date->toDateString(),
                'unique:rab_days,day_date,' . $day->id . ',id,rab_period_id,' . $rabPeriod->id,
            ],
            'pk_count'   => 'required|integer|min:0',
            'pb_count'   => 'required|integer|min:0',
            'realisasi'  => 'nullable|numeric|min:0',
        ]);

        $day->update($data);

        return redirect()->route('rab-periods.show', $rabPeriod)
            ->with('success', 'Day updated.');
    }

    public function destroy(RabPeriod $rabPeriod, RabDay $day)
    {
        $day->delete();
        return redirect()->route('rab-periods.show', $rabPeriod)
            ->with('success', 'Day removed.');
    }
}
