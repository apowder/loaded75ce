{use class="frontend\design\Info"}
<div class="col-left">
  <div class="demo-heading-3">Preview</div>

  <div class="products-listing list-type-2">
    <div class="item" style="width: 49%">
      <div class="title"><a>Title (productname)</a></div>
      <div class="stock">
        <span class="in-stock"><span class="in-stock-icon">&nbsp;</span>In stock</span>
      </div>
      <div class="image">
        <a><img src="themes/theme-1/img/na.png" alt="ball" title="ball"></a>
        <span class="sale"></span>
      </div>

      <div class="description"><p>Product short description. Some random text placed here, just to fill in the white space</p></div>
      <div class="products-model"><strong>Model<span class="colon">:</span></strong> <span>1234</span></div>
      <div class="properties">
        <div class="property">
          <strong>Property 1<span class="colon">:</span></strong>
          <span>34.6</span>
        </div>
        <div class="property">
          <strong>Property 2<span class="colon">:</span></strong>
          <span>10 - 20</span>
        </div>
      </div>


      <div class="add-height">
        <div class="rating-count">(5)</div>
        <div class="rating">
          <span class="rating-3"></span>
        </div>
        <div class="price">
          <span class="old">£12.00</span>
          <span class="specials">£10.00</span>
        </div>
        <div class="qty-input">
          <label>{output_label const="QTY"}</label>
          <input type="text" name="qty" value="1" class="qty-inp"/>
        </div>
        <div class="buy-button">
          <a class="btn-1 btn-buy add-to-cart set-popup" title="Add to Cart"></a>
        </div>
      </div>

      <div class="buttons">
        <div class="button-wishlist">
          <button type="submit">Save</button>
        </div>
        <div class="button-view">
          <a class="view-button">View</a>
        </div>
      </div>
      <div class="compare-box-item">
        <label>
          <span class="cb_title">Select to compare</span>
          <span class="cb_check"><input type="checkbox" name="compare[]" value="" class="checkbox"><span>&nbsp;</span></span>
        </label>
      </div>
    </div>

  </div>


</div>
<div class="col-right">
  <div class="demo-heading-3">Edit</div>

  <div class="edit-list edit-list-type-2"{Info::dataClass('.list-type-2')}>
    <div class="edit-item"{Info::dataClass('.list-type-2 .item')} style="width: 49%">
      <div class="edit-title"{Info::dataClass('.list-type-2 .title')}><a{Info::dataClass('.list-type-2 .title a')}>Title (productname)</a></div>
      <div class="edit-stock"{Info::dataClass('.list-type-2 .stock')}>
        <span class="in-stock"><span class="in-stock-icon">&nbsp;</span>Delivery terms label</span>
      </div>
      <div class="edit-image"{Info::dataClass('.list-type-2 .image')}>
        <a{Info::dataClass('.list-type-2 .image a')}><img src="themes/theme-1/img/na.png" alt="ball" title="ball"></a>
        <span class="edit-sale"{Info::dataClass('.list-type-2 .sale')}></span>
      </div>

      <div class="edit-description"{Info::dataClass('.list-type-2 .description')}><p>Product short description. Some random text placed here, just to fill in the white space</p></div>
      <div class="edit-products-model"{Info::dataClass('.list-type-2 .products-model')}>
        <strong{Info::dataClass('.list-type-2 .products-model strong')}>Model<span class="colon">:</span></strong>
        <span{Info::dataClass('.list-type-2 .products-model > span')}>1234</span>
      </div>
      <div class="edit-properties"{Info::dataClass('.list-type-2 .properties')}>
          <div class="edit-property"{Info::dataClass('.list-type-2 .property')}>
            <strong{Info::dataClass('.list-type-2 .property strong')}>Property 1<span class="colon">:</span></strong>
            <span{Info::dataClass('.list-type-2 .property > span')}>34.6</span>
          </div>
          <div class="edit-property"{Info::dataClass('.list-type-2 .property')}>
            <strong{Info::dataClass('.list-type-2 .property strong')}>Property 2<span class="colon">:</span></strong>
            <span{Info::dataClass('.list-type-2 .property > span')}>10 - 20</span>
          </div>
        </div>

      <div class="edit-add-height"{Info::dataClass('.list-type-2 .add-height')}>
        <div class="edit-rating-count"{Info::dataClass('.list-type-2 .rating-count')}>(5)</div>
        <div class="edit-rating"{Info::dataClass('.list-type-2 .rating')}>
          <span class="rating-3"></span>
        </div>
        <div class="edit-price"{Info::dataClass('.list-type-2 .price')}>
          <span class="current"{Info::dataClass('.list-type-2 .price .current')}>£10.00</span>
        </div>
        <div class="edit-price"{Info::dataClass('.list-type-2 .price')}>
          <span class="old"{Info::dataClass('.list-type-2 .price .old')}>£12.00</span>
          <span class="specials"{Info::dataClass('.list-type-2 .price .specials')}>£10.00</span>
        </div>
        <div class="edit-qty-input"{Info::dataClass('.list-type-2 .qty-input')}>
          <label{Info::dataClass('.list-type-2 .qty-input label')}>{output_label const="QTY"}</label>
          <span class="edit-qty"{Info::dataClass('.list-type-2 .qty-inp')}><input type="text" name="qty" value="1" class="qty-inp"/></span>
        </div>
        <div class="edit-buy-button"{Info::dataClass('.list-type-2 .buy-button')}>
          <a class="edit-btn-buy" title="Add to Cart"{Info::dataClass('.list-type-2 .buy-button .btn-buy, .list-type-2 .buy-button .btn-cart')}></a>
        </div>
      </div>
      <div class="edit-buttons"{Info::dataClass('.list-type-2 .buttons')}>
        <div class="edit-button-wishlist"{Info::dataClass('.list-type-2 .button-wishlist')}>
          <button type="submit"{Info::dataClass('.list-type-2 .button-wishlist button')}>Save</button>
        </div>
        <div class="edit-button-view"{Info::dataClass('.list-type-2 .button-view')}>
          <a class="edit-view-button"{Info::dataClass('.list-type-2 .button-view a')}>View</a>
        </div>
      </div>
      <div class="edit-compare-box-item"{Info::dataClass('.list-type-2 .compare-box-item')}>
        <label{Info::dataClass('.list-type-2 .compare-box-item label')}>
          <span class="cb_title">Select to compare</span>
          <span class="cb_check"><input type="checkbox" name="compare[]" value="" class="checkbox"><span>&nbsp;</span></span>
        </label>
      </div>
    </div>

  </div>
</div>