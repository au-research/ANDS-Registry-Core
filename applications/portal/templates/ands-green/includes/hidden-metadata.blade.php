<input type="hidden" id="ro_id" value="{{$ro->core['id']}}"></input>
<input type="hidden" id="ro_slug" value="{{$ro->core['slug']}}"></input>
<input type="hidden" id="ro_key" value="{{$ro->core['key']}}"></input>
<input type="hidden" id="ro_title" value="{{$ro->core['title']}}"></input>
<input type="hidden" id="ro_group" value="{{$ro->core['group']}}"></input>
<input type="hidden" id="ro_class" value="{{$ro->core['class']}}"></input>

@if($this->input->get('refer_q'))
<input type="hidden" id="refer_q" value="{{ $this->input->get('refer_q') }}"></input>
@endif