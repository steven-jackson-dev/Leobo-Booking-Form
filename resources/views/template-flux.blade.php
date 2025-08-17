{{--
  Template Name: Flux Template
--}}

@extends('layouts.app')

@section('content')
  @while(have_posts()) @php(the_post())
    @include('partials.content-flux')
  @endwhile
@endsection
