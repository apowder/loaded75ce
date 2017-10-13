{use class="frontend\design\Info"}

<div style="margin: 30px 0 30px;">

  <div class="demo-heading-3">Preview</div>
  <div class="box-block type- tabs" style="font-weight:normal;" data-name="Tabs" id="box-0">
    <div class="tab-navigation">
      <div class="tab-block-0-1">
        <span data-href="#tab-block-0-1" class="active">tab 1</span>
      </div>
      <div class="tab-block-0-2">
        <span data-href="#tab-block-0-2" class="">tab 2</span>
      </div>
      <div class="tab-block-0-2">
        <span data-href="#tab-block-0-2" class="">tab 3</span>
      </div>
    </div>
    <div class="block" id="tab-block-0-1" style="display: block;">
      Tab content
    </div>
    <div class="block" id="tab-block-0-2" style="display: none;">
      Tabs 2 content
    </div>
    <div class="block" id="tab-block-0-3" style="display: none;">
      Tabs 3 content
    </div>
  </div>


  <div class="demo-heading-3">Edit</div>

  <div class="edit-tabs"{Info::dataClass('.tabs')}>
    <div class="edit-tab-navigation"{Info::dataClass('.tab-navigation')}>
      <div{Info::dataClass('.tab-navigation > div, .tab-navigation > li')}>
        <span class=""{Info::dataClass('.tab-navigation > div > span, .tab-navigation > li > span, .tab-navigation > div > a, .tab-navigation > li > a')}>tab 1</span>
      </div>
      <div{Info::dataClass('.tab-navigation > div, .tab-navigation > li')}>
        <span class=""{Info::dataClass('.tab-navigation > div > span, .tab-navigation > li > span, .tab-navigation > div > a, .tab-navigation > li > a')}>tab 2</span>
      </div>
      <div{Info::dataClass('.tab-navigation > div, .tab-navigation > li')}>
        <span class=""{Info::dataClass('.tab-navigation > div > span, .tab-navigation > li > span, .tab-navigation > div > a, .tab-navigation > li > a')}>tab 3</span>
      </div>
    </div>
    <div class="edit-block" style="display: block;"{Info::dataClass('.tabs > .block')}>
      Tab content
    </div>
  </div>

</div>