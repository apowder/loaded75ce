
<div class="contacts {if $settings[0].show_icons}contacts-icons{/if}" itemscope itemtype="http://schema.org/Store">
  <h4><strong>{$data.company}</strong></h4>

  <address itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
    {$address}
  </address>

  <table>
    <tr>
      <td class="phone"><strong>{output_label const="ENTRY_TELEPHONE_NUMBER"}</strong></td>
      <td><span itemprop="telephone" content="{$phone}">{$data.telephone}</span></td>
    </tr>
    <tr>
      <td class="email"><strong>{output_label const="TEXT_EMAIL"}</strong></td>
      <td><span itemprop="email">{$data.email_address}</span></td>
    </tr>

  {if $data.reg_number neq ''}
    <tr>
      <td class="reg-number"><strong>{output_label const="TEXT_REG_NUMBER"}</strong></td>
      <td><span itemprop="leiCode">{$data.reg_number}</span></td>
    </tr>
  {/if}
  {if $data.company_vat neq ''}
    <tr>
      <td class="company-vat"><strong>{output_label const="ENTRY_BUSINESS"}</strong></td>
      <td><span itemprop="vatID">{$data.company_vat}</span></td>
    </tr>
  {/if}
  </table>
  
  <div class="hours" style="margin-top: 20px;"><strong>{$smarty.const.TEXT_OPENING_HOURS}</strong></div>

  <div class="hours-content">
  {foreach $data.open as $item}

    <meta itemprop="openingHours" content="{$item.days_short} {$item.time_from}-{$item.time_to}">
    <p>{$item.days_short} (<time datetime="{$item.time_from}">{$item.time_from}</time>-<time datetime="{$item.time_to}">{$item.time_to}</time>)</p>

  {/foreach}
  </div>
</div>