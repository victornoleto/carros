<div id="page-with-filters" class="d-flex">

	<x-filters />

	<div class="flex-grow-1 position-relative overflow-hidden">

		<div id="page-inner" class="position-absolute top-0 left-0 w-100 h-100 p-4 overflow-y-auto">

			{{ $slot }}

		</div>

	</div>

</div>