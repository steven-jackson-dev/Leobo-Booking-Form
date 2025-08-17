@if(isset($content->hide_section) && $content->hide_section == 'no')
    <section @if(!empty($content->id)) id="{!! $content->id !!}" @endif class="leo-main-intro scroll_fadeani px-7 md:px-10 py-14 md:py-20 lg:py-24 flex flex-col items-center gap-7 @if(!empty($content->extra_class)){!! $content->extra_class !!}@endif">
        @if(!empty($content->scroll_headings))
            @foreach ($content->scroll_headings as $item)
                <h2 class="text-center !text-[22px] md:!text-[30px] lg:!text-[38px]">{!! $item['text'] !!}</h2>
            @endforeach
        @endif
    </section>
@endif
