<div class="modal-body swatch-white">
    <div class="container-fluid">
         <div class="row">
              <div class="col-md-12">
                   <div class="form-group">
                        <label for="">Title</label> {{$related['title']}}
                   </div>
                   <div class="form-group">
                        <label for="">Relation</label>
                       <?php
                       if(is_array($related['relationship'])){
                       foreach($related['relationship'] as $relationship)
                            echo readable($relationship);
                       }else{
                           echo readable($related['relationship']);
                       }
?>
                   </div>
                   @if(isset($related['URL']))
                        <div class="form-group">
                            <label for="">URL</label> <a href="{{$related['URL']}}">{{$related['URL']}}</a>

                        </div>
                   @endif
                   @if(isset($related['email']))
                        <div class="form-group">
                            <label for="">Email</label> {{$related['email']}}

                        </div>
                   @endif
                  @if(isset($related['address']))
                  <div class="form-group">
                      <label for="">Address</label> {{$related['address']}}
                  </div>
                  @endif
                   @if(isset($related['phone']))
                        <div class="form-group">
                            <label for="">Phone</label> {{$related['phone']}}
                        </div>
                   @endif
                   @if(isset($sub_type)&& $sub_type=='publisher')
                      @if($related['type']=='party' && isset($related['other_vocabs'][0]['title']))
                      <div class="form-group">
                          <label for="">Also published</label><br />
                          @foreach($related['other_vocabs'] as $other_vocab)
                          <a href="{{base_url().$other_vocab['slug']}}">{{$other_vocab['title']}}</a><br />
                          @endforeach
                      </div>
                  @endif
                  @elseif(isset($related['other_vocabs'][0]['title']))
                    @if($related['type']=='party')
                  <div class="form-group">
                      <label for="">Also related to vocabularies</label><br />
                      @foreach($related['other_vocabs'] as $other_vocab)
                      <a href="{{base_url().$other_vocab['slug']}}">{{$other_vocab['title']}}</a><br />
                      @endforeach
                  </div>
                  @endif
                  @if($related['type']=='vocabulary')
                  <div class="form-group">
                      <label for="">View related vocabulary</label>
                      @foreach($related['other_vocabs'] as $other_vocab)
                      <a href="{{base_url().$other_vocab['slug']}}">{{$other_vocab['title']}}</a><br />
                      @endforeach
                  </div>
                  @endif
                  @endif
              </div>
         </div>
    </div>
</div>