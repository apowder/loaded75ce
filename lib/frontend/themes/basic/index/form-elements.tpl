{use class="frontend\design\Info"}

<div class="design-box-elements">
  <div {Info::dataClass("input[type='text'], input[type='password'], input[type='number'], input[type='email'], select")}><input type="text" value="input field"/></div>

  <div {Info::dataClass('textarea')}><textarea name="" id="" cols="30" rows="10">textarea field</textarea></div>
</div>