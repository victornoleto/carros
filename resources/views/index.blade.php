<x-layout>

    <x-page-with-filters>

        <div id="charts-row" class="row">
    
            <div class="col-12 col-lg-6 mb-4 d-none">
    
                <div class="card">
    
                    <div class="card-header d-flex align-items-center">
                        
                        <b data-attribute="brand-model"></b>
    
                        <div class="controls ms-auto d-flex gap-3">
                            <i data-action="expand" class="fas fa-expand"></i>
                            {{-- <i class="fas fa-eye-slash"></i> --}}
                            <i data-action="table" class="fas fa-table"></i>
                        </div>
    
                    </div>
    
                    <div class="card-body">
                        <canvas class="w-100"></canvas>
                    </div>
    
                </div>
    
            </div>

        </div>

        <div id="chart-modal" class="modal" tabindex="-1" role="dialog">

            <div class="modal-dialog" role="document">

                <div class="modal-content">

                    <div class="modal-header">

                        <h5 class="modal-title fw-bold"></h5>

                        <button type="button" class="btn btn-sm btn-dark fw-bold" data-action="close">Fechar</button>

                    </div>

                    <div class="modal-body"></div>

                </div>

            </div>

        </div>

    </x-page-with-filters>

    <script src="{{ asset('assets/js/color-gradient.js') }}"></script>
    <script src="{{ asset('assets/js/tooltip.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/select2.js') }}"></script>

</x-layout>