@if(isset($content->hide_section) && $content->hide_section == 'no')
    <section @if(!empty($content->id)) id="{!! $content->id !!}" @endif class="leo-gallery-tabs px-7 md:px-10 py-14 md:py-20 lg:py-24 @if(!empty($content->extra_class)){!! $content->extra_class !!}@endif">
        <div class="leo-tabbed-listing__tabs tabs">
            <div class="leo-tabs__tab-list flex gap-10">
                @php $iteration = 1 @endphp
                @foreach($content->tabs as $tab)
                    <div
                    id="tab-{!! $iteration !!}"
                    class="leo-tabs__tab-list__item"
                    >
                        {!! $tab['tab_name'] !!}
                        <span>{!! $tab['tab_name'] !!}</span>
                    </div>
                    @php $iteration += 1 @endphp
                @endforeach
            </div>
            @php $iterationTwo = 1 @endphp
            @foreach($content->tabs as $tab)
                <div id="tab-{!! $iterationTwo !!}-content" class="leo-tabs__tab">
                    <div class="leo-gallery-grid flex flex-col gap-[40px]">
                        @if(!empty($tab['gallery']))
                            @foreach($tab['gallery'] as $gallery)
                                @foreach($gallery as $row)
                                    <div class="leo-gallery-row anim_fadeinup flex flex-col md:flex-row gap-[25px] h-auto md:h-[34vw]">
                                        @foreach($row as $item)
                                            <a href="{!! $item['image']['url'] !!}" class="block @if($item['image_size'] === 'medium') leo-img-medium @elseif($item['image_size'] === 'small') leo-img-small @elseif($item['image_size'] === 'large') leo-img-large @endif" data-fancybox="{{ $iterationTwo }}">
                                                <img src="{!! $item['image']['url'] !!}" loading="lazy" alt="{!! $item['image']['alt'] !!}" class="leo-gallery-image object-cover object-center h-full w-full">
                                            </a>
                                        @endforeach
                                    </div>
                                @endforeach
                            @endforeach
                        @endif
                    </div>
                </div>
                @php $iterationTwo += 1 @endphp
            @endforeach
        </div>
    </section>
@endif
