<?php
// =============================================================================
// TraceId Middleware — Log ↔ Trace Correlation
// =============================================================================
// Copy this file to: app/Http/Middleware/TraceIdMiddleware.php
//
// This middleware injects the current OpenTelemetry trace ID into every log
// entry during the request lifecycle, enabling one-click navigation from
// a Loki log line to its full Tempo trace in Grafana.
// =============================================================================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use OpenTelemetry\API\Trace\Span;

class TraceIdMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $span    = Span::getCurrent();
        $traceId = $span->getContext()->getTraceId();

        if ($traceId && $traceId !== '00000000000000000000000000000000') {
            // Inject traceId into all log entries for this request.
            // Alloy's loki.process pipeline extracts this for Loki↔Tempo linking.
            app('log')->shareContext(['traceId' => $traceId]);
        }

        return $next($request);
    }
}
