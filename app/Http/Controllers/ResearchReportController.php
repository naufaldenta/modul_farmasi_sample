<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ResearchEvaluationService;

class ResearchReportController extends Controller
{
    public function index(ResearchEvaluationService $evaluator)
    {
        // Because data volume is high and calculations take time, we only evaluate if requested
        $evaluate = request()->get('evaluate', false);
        
        $compliance = null;
        $waste = null;
        $forecast = null;

        if ($evaluate) {
            $compliance = $evaluator->calculateFefoCompliance();
            $waste = $evaluator->simulateWaste();
            $forecast = $evaluator->evaluateForecastSplit(9, 3, 3);
        }

        return inertia('Inventory/ResearchReport', [
            'hasData' => $evaluate,
            'compliance' => $compliance,
            'waste' => $waste,
            'forecast' => $forecast,
        ]);
    }
}
