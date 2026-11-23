{* EU label of the manufacturer's durability guarantee (Regulation (EU) 2025/1960),
   a thumbnail that opens the full label - this nested display is allowed for the label *}
{if is_array($product_guarantee_label)}
    <div class="product-guarantee-label mt-3">
        <a href="{$product_guarantee_label.src}" class="lightbox" title="{$lang_label_guarantee_label}">
            <img src="{$product_guarantee_label.src}" alt="{$product_guarantee_label.alt}" class="img-fluid border rounded" style="max-height: 120px;" loading="lazy">
        </a>
    </div>
{/if}
