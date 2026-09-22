{{-- Renders a widget zone (home_top or home_bottom) --}}
{{-- $zoneWidgets: Collection of Widget models --}}
{{-- $widgetData: array of pre-fetched data keyed by widget type --}}
@if(!empty($zoneWidgets) && $zoneWidgets->isNotEmpty())
<div class="mb-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($zoneWidgets as $widget)
        @include('partials._widget', ['widget' => $widget, 'data' => $widgetData])
    @endforeach
</div>
@endif
