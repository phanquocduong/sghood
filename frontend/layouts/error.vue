<template>
    <div id="titlebar">
	<div class="container">
		<div class="row">
			<div class="col-md-12">

				<h2>404 Not Found</h2>

				<!-- Breadcrumbs -->
				<nav id="breadcrumbs">
					<ul>
						<li><a href="#">Home</a></li>
						<li>404 Not Found</li>
					</ul>
				</nav>

			</div>
		</div>
	</div>
</div>


<!-- Content
================================================== -->


<!-- Container -->
<div class="container">

	<div class="row">
		<div class="col-md-12">

			<section id="not-found" class="center">
				<h2>404 <i class="fa fa-question-circle"></i></h2>
				<p>We're sorry, but the page you were looking for doesn't exist.</p>

				<!-- Search -->
				<div class="row">
					<div class="col-lg-8 col-lg-offset-2">
						<div class="main-search-input gray-style margin-top-50 margin-bottom-10">
							<div class="main-search-input-item">
								<input type="text" placeholder="What are you looking for?" value=""/>
							</div>

							<button class="button">Search</button>
						</div>
					</div>
				</div>
				<!-- Search Section / End -->


			</section>

		</div>
	</div>

</div>
</template>

<script>
definePageMeta({layout:'default'})
defineProps({
  error: Object
})
</script>

<style >

</style>