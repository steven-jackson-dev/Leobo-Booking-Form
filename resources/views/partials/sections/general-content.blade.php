@if(isset($content->hide_section) && $content->hide_section == 'no')
    <section @if(!empty($content->id)) id="{!! $content->id !!}" @endif class="leo-general-content px-7 md:px-10 py-14 md:py-20 lg:py-24 @if(!empty($content->extra_class)){!! $content->extra_class !!}@endif">
        @if(!empty($content->general_content))
            <div class="leo-inner-wrapper bg-maroonLight px-7 py-10 md:py-16 mx-auto max-w-[1150px]">
                <div class="leo-general-content mx-auto max-w-[800px]">
                    {!! $content->general_content !!}
                </div>
            </div>
        @endif
    </section>
@endif
