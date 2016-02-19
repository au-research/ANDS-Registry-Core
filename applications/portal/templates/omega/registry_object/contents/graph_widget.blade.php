<div class="swatch-white">
    <div class="panel panel-primary element-no-top element-short-bottom panel-content">
        <!-- <div class="panel-heading"> Dates </div> -->
        <div class="panel-body swatch-white">
            <div class="graph" style="visibility: visible;">
            </div>

            <script>
            $(document).ready(function() {
                var reqkey = "{{ $ro->core['key'] }}"
                var accesskey = "Eiphoh3xieXoosha"

                graph_init(reqkey, accesskey);
            });
            </script>
        </div>
    </div>
</div>