@extends('installer::layout')

@section('body')
	<div class="card performInstall">
	  <div class="card-header">
	    Installing...
	  </div>
	  <div class="card-body">
	  	<div class="error d-none text-danger">
	  		Error encountered :
	  		<p class="message"></p>
	  	</div>
	  	<div class="step" data-url="{{ route('install.steps.env')}}">
	  		<p>Write env file... <i class="float-right fa fa-check d-none"></i></p>
	  	</div>
	  	<div class="step" data-url="{{ route('install.steps.modules')}}">
	  		<p>Enable modules... <i class="float-right fa fa-check d-none"></i></p>
	  	</div>
	  	<div class="step" data-url="{{ route('install.steps.migrate')}}">
	  		<p>Migrate laravel... <i class="float-right fa fa-check d-none"></i></p>
	  	</div>
	  	<div class="step" data-url="{{ route('install.steps.migrateModules')}}">
	  		<p>Migrate modules... <i class="float-right fa fa-check d-none"></i></p>
	  	</div>
	  	<div class="step" data-url="{{ route('install.steps.seed')}}">
	  		<p>Seed modules... <i class="float-right fa fa-check d-none"></i></p>
	  	</div>
	  	<div class="step" data-url="{{ route('install.steps.node')}}">
	  		<p>Install node modules... <i class="float-right fa fa-check d-none"></i></p>
	  	</div>
	  	<div class="step" data-url="{{ route('install.steps.assets')}}">
	  		<p>Build assets... <i class="float-right fa fa-check d-none"></i></p>
	  	</div>
	  	<div class="step" data-url="{{ route('install.steps.symStorage')}}">
	  		<p>Symlink storage... <i class="float-right fa fa-check d-none"></i></p>
	  	</div>
	  	<div class="step" data-url="{{ route('install.steps.symThemes')}}">
	  		<p>Symlink themes... <i class="float-right fa fa-check d-none"></i></p>
	  	</div>
	  	<div class="step" data-url="{{ route('install.steps.cache')}}">
	  		<p>Clear cache... <i class="float-right fa fa-check d-none"></i></p>
	  	</div>
	  	<p class="success d-none text-success">
	  		Installation complete!
	  	</p>
	  </div>
	  <div class="card-footer text-muted text-right">
	    <a href="/" class="d-none btn btn-primary visit">Visit my site</a>
	  </div>
	</div>
@endsection