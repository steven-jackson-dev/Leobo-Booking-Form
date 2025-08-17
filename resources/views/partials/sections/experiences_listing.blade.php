<section class="bg-maroonLight px-7 md:px-10 py-14 md:py-20 lg:py-24">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 experience-grid">
        @foreach($content->experience_listing_arr as $post)
            <a href="{!! $post['url'] !!}" class="rounded-[5px]">
                <div class="relative rounded-[5px]">
                    <img class="w-full h-auto rounded-[5px]" src="{!! $post['img'] !!}" />
                    <p class="absolute bottom-0 left-0 right-0 text-[22px] p-4">
                        {!! $post['title'] !!}
                    </p>
                </div>
            </a>
        @endforeach
    </div>

    <div class="mt-10 flex justify-center items-center exlist__loadmorebtn">
        <a href="javascript:void(0)" class="leo-btn-tertiary" id="load-more-btn">
            Load more experiences
        </a>
    </div>
</section>