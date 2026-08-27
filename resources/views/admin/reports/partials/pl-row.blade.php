@php
    // Variables injected by @include:
    //   $label   (string)
    //   $indent  (int: 0|1)
    //   $curr    (float)
    //   $prev    (float|null) — null means comparison mode is off for this row
    //   $color   (string — bootstrap color name or '')
    //   $deduction    (bool, optional) — show value in parentheses if negative
    //   $italic       (bool, optional)
    //   $separator_top (bool, optional)
    //
    // The partial MUST NOT read $report from parent scope.
    // Whether to show comparison columns is determined purely by $prev !== null.

    $indent      = $indent ?? 0;
    $deduction   = $deduction ?? false;
    $italic      = $italic ?? false;
    $sepTop      = $separator_top ?? false;
    $color       = $color ?? '';

    // $prev being non-null means comparison mode is active for this row
    $hasPrev = isset($prev) && $prev !== null;

    $fmtVal = function(float $val) use ($deduction): string {
        if ($deduction && $val < 0) {
            return '(Rs.&nbsp;' . number_format(abs($val), 2) . ')';
        }
        return 'Rs.&nbsp;' . number_format($val, 2);
    };

    $variance = $hasPrev ? ($curr - $prev) : null;
@endphp
<tr class="{{ $sepTop ? 'border-top' : '' }}">
    <td class="{{ $indent ? 'ps-4' : 'ps-3' }} {{ $italic ? 'fst-italic' : '' }} text-muted small">
        {{ $label }}
    </td>
    <td class="text-end {{ $color ? 'text-'.$color : '' }} small">
        {!! $fmtVal((float)$curr) !!}
    </td>
    @if($hasPrev)
    <td class="text-end text-muted small">
        {!! $fmtVal((float)$prev) !!}
    </td>
    <td class="text-end small {{ $variance >= 0 ? 'text-success' : 'text-danger' }}">
        {{ $variance >= 0 ? '+' : '' }}Rs.&nbsp;{{ number_format($variance, 2) }}
    </td>
    @elseif(isset($c) && $c !== null)
    {{-- Comparison mode is on but $prev was not passed — show dashes --}}
    <td class="text-end text-muted small">—</td>
    <td class="text-end text-muted small">—</td>
    @endif
</tr>
