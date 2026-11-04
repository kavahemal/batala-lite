<?php

use Carbon\Carbon;

if (! function_exists('getAjaxDatatableUrl')) {
    function getAjaxDatatableUrl()
    {
        return request()->isSecure() ? secure_url(request()->path()) : url(request()->path());
    }
}

if (! function_exists('dateTimeFormat')) {
    function dateTimeFormat($dateTime = null, $format = 'date')
    {
        if (empty($dateTime)) {
            return '';
        }

        if ($format === '') {
            $format = 'date';
        }

        $format = match ($format) {
            'date' => 'd M, Y',
            'short_date' => 'd M',
            'date_time' => 'd M Y, h:i A',
            'time' => 'h:i A',
            'short_time' => 'H:i',
            default => $format,
        };

        try {
            return Carbon::parse($dateTime)->format($format);
        } catch (\Exception $e) {
            return '';
        }
    }
}
