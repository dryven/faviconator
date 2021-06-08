@extends('statamic::layout')

@section('content')
	<publish-form
		title="{{ $title }}"
		action="{{ $action }}"
		:blueprint='@json($blueprint)'
		:meta='@json($meta)'
		:values='@json($values)'>
	</publish-form>

	@include('statamic::partials.docs-callout', [
		'topic' => 'Faviconator',
		'url' => 'https://statamic.com/addons/ddm-studio/faviconator'
	])
@stop
