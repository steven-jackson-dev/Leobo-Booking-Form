@if(isset($content->hide_section) && $content->hide_section == 'no')
    <section class="bg-[#AA8471] relative">
        <img src="@asset('images/charcoal-bg-high-res.png')" loading="lazy" alt="" class="leo-testimonial-bg-img absolute object-cover top-0 left-0 w-full h-full z-[6]">
        <div class="anim_fadeinup relative z-[7] flex flex-col gap-7 items-center justify-center h-[max-content] w-full py-10 md:py-20 lg:py-24 px-7 md:px-10">
            <img src="@asset('images/icons/quotes.svg')" loading="lazy" width="127" height="108" class="absolute h-[108px] object-contain top-20 left-[15vw]" alt="">
            @if(!empty($content->testimonial_text))
                <p
                class="uppercase text-lightSand font-heading font-bold text-[28px] md:text-[34px] lg:text-[38px] text-center w-full leading-[1.3em] tracking-[0.05em] relative max-w-[1050px] mx-auto mt-16">
                    {!! $content->testimonial_text !!}
                </p>
            @endif
            @if(!empty($content->testimonial_logo))
            <a href="/press/">
                <img src="{!! $content->testimonial_logo['url'] !!}" loading="lazy" alt="testimonial author" class="leo-testimonial-img">
            </a>
            @endif
        </div>
    </section>
@endif
