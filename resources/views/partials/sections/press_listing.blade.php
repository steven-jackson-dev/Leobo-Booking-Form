<section>

    <div class="!grid !grid-cols-1 lg:!grid-cols-3 gap-[48px] py-[50px] px-[42px]" style="display: grid;">


        @php $iteration = 1 @endphp

        @foreach ($content->press_item as $press_item)
            <div class="press-item flex flex-col justify-center items-center bg-maroonLight h-[400px]">
                <a href="{!! $press_item['link']['url'] !!}">
                    <div class="press-img w-[200px] h-[50px]">
                        <img src="{!! $press_item['logo']['url'] !!}" width="200" height="50" />
                    </div>
                    <div class="w-full text-center pt-10">
                        <p>{!! $press_item['date'] !!}</p>
                        <h6>{!! $press_item['title'] !!}</h6>
                    </div>
                </a>
            </div>

            @php $iteration += 1 @endphp
        @endforeach

    </div>

</section>
