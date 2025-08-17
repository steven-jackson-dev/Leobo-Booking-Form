@if($fluxContetData)
	@foreach($fluxContetData as $content)

        @if($content->layout == 'hero')
            @include('partials.sections.hero')

        @elseif($content->layout == 'intro')
            @include('partials.sections.intro')	
        
         @elseif($content->layout == 'get_in_touch')
            @include('partials.sections.get_in_touch')	
        
        @elseif($content->layout == 'stacked_media_content')
            @include('partials.sections.stacked_media_content')	

        @elseif($content->layout == 'full_width_banner')
            @include('partials.sections.full_width_banner')	
        
        @elseif($content->layout == 'press_listing')
            @include('partials.sections.press_listing')	

        @elseif($content->layout == 'video_banner')
            @include('partials.sections.video_banner')	

        @elseif($content->layout == 'image_content')
		        @include('partials.sections.image_content')

        @elseif($content->layout == 'image_content_with_tabs')
		    @include('partials.sections.image-content-with-tabs')

        @elseif($content->layout == 'image_content_with_popup')
		    @include('partials.sections.image-content-with-popup')

        @elseif($content->layout == 'media_content')
		    @include('partials.sections.media-content')

        @elseif($content->layout == 'general_content')
		    @include('partials.sections.general-content')
            
        @elseif($content->layout == 'intro_content')
            @include('partials.sections.intro-content')	
        
        @elseif($content->layout == 'accordion')
            @include('partials.sections.accordion')	

        @elseif($content->layout == 'main_intro')
            @include('partials.sections.main-intro')	

        @elseif($content->layout == 'awards_slider')
            @include('partials.sections.awards-slider')
            
        @elseif($content->layout == 'highlights_slider')
            @include('partials.sections.highlights-slider')

        @elseif($content->layout == 'experiences_slider')
            @include('partials.sections.experiences-slider')

        @elseif($content->layout == 'experiences_listing')
            @include('partials.sections.experiences_listing')

        @elseif($content->layout == 'gallery_tabs')
            @include('partials.sections.gallery-tabs')

        @elseif($content->layout == 'rates_tabs')
            @include('partials.sections.rates-tabs')
        
        @elseif($content->layout == 'stay_banner')
            @include('partials.sections.stay_banner')

        @elseif($content->layout == 'related_cards')
            @include('partials.sections.related-cards')

        @elseif($content->layout == 'testimonials')
            @include('partials.sections.testimonials')

        @endif

	@endforeach
@endif