/*
  Copyright 2013 The Australian National University
  Licensed under the Apache License, Version 2.0 (the "License");
  you may not use this file except in compliance with the License.
  You may obtain a copy of the License at

  http://www.apache.org/licenses/LICENSE-2.0

  Unless required by applicable law or agreed to in writing, software
  distributed under the License is distributed on an "AS IS" BASIS,
  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
  See the License for the specific language governing permissions and
  limitations under the License.
*******************************************************************************/
;(function($) {

    $.fn.ro_summary_widget = function() {

        var defaults = {
            //jsonp  endpoint
            endpoint: 'http://researchdata.ands.org.au/apps/registry_summary'
        }

        //ANDS Environment
        if (typeof(window.real_base_url) !== 'undefined'){
            defaults['endpoint'] = window.real_base_url + 'apps/registry_summary';
        }

        $.ajax({
            url:defaults['endpoint']+'?callback=?',
            dataType: 'JSONP',
            timeout: 1000,
            success: function(data){
                $('.rosummary').html(data.theHtml);
            },
            error: function(xhr){
                //console.log(xhr)
            }
        });
    }
    $('.rosummary').ro_summary_widget();
})( jQuery );
