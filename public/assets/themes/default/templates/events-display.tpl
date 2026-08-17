<div class="event-entry">
    <h1>{$event_title}</h1>

    <div class="row mb-3">
        <div class="col-md-2">
            <div class="event-date">
                <div class="event-date-header">
                    <span class="event-start-day">{$event_start_day}.</span>
                    <span class="event-start-month">{$event_start_month_text}</span>
                </div>
                <span class="event-start-year">{$event_start_year}</span>
                <div class="event-date-footer">
                    <span class="event-end-date">{$event_end_day}.{$event_end_month}.{$event_end_year}</span>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <p><span class="post-author">{$event_author}</span> <span class="post-releasedate">{$event_releasedate}</span></p>
            {$event_teaser}
            {if $content_tags == true}
                <p class="m-0 content-tags">
                    {foreach $content_tags as $tag}
                        <a href="{$tag.tag_href}" class="btn btn-sm btn-link" title="{$tag.tag_title}">#{$tag.tag_title}</a>
                    {/foreach}
                </p>
            {/if}
        </div>
        <div class="col-md-3">
            <p><img src="{$event_img_src}" class="img-fluid" alt="{$event_img_caption}"><br><small>{$event_img_caption}</small></p>
        </div>
    </div>

    <div class="post-text mb-3">
        {$event_text}
    </div>

    {if $show_guestlist == true}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <p class="h4">{$label_guestlist}</p>
                    <p>{$description_guestlist}</p>
                </div>
                <div class="col-md-4">
                    <dl class="row">
                        <dt class="col-sm-9 text-end">{$label_nbr_total_available}</dt>
                        <dd class="col-sm-3">{$nbr_available_total}</dd>
                        <dt class="col-sm-9 text-end">{$label_nbr_commitments}</dt>
                        <dd class="col-sm-3">
                            {if $label_nbr_commitments != ""}
                                <span id="nbr-commitments" hx-get="/xhr/se/guestlist/?evc={$event_id}" hx-swap="innerHTML" hx-trigger="load, update_guestlist_{$event_id} from:body">{$nbr_commitments}</span>
                            {else}
                                <span id="nbr-commitments">{$nbr_commitments}</span>
                            {/if}
                        </dd>
                    </dl>
                </div>
            </div>

            {$hidden_csrf_token}
            <button class="btn btn-sm btn-outline-secondary" hx-post="/xhr/se/guestlist/" hx-swap="none" hx-include="[name='csrf_token']" name="val" value="confirm-{$event_id}" {$disabled}>{$sign_guestlist}</button>
        </div>
    </div>
    {/if}

    <div class="post-text mb-3">
        {$event_price_note}
    </div>

    {if $show_voting == true}
        <div class="mb-3">
            {$hidden_csrf_token}
            <button class="btn btn-sm btn-outline-secondary" hx-post="/xhr/se/vote/" hx-swap="none" hx-include="[name='csrf_token']"
                    name="vote" value="up-event-{$event_id}" {$votes_status_up}>
                <i class="bi bi-hand-thumbs-up-fill"></i> <span hx-get="/xhr/se/votes/?section=e&upv={$event_id}" hx-swap="innerHTML" hx-trigger="load, update_votings_{$event_id} from:body">0</span>
            </button>
            <button class="btn btn-sm btn-outline-secondary" hx-post="/xhr/se/vote/" hx-swap="none" hx-include="[name='csrf_token']"
                    name="vote" value="dn-event-{$event_id}" {$votes_status_dn}>
                <i class="bi bi-hand-thumbs-down-fill"></i> <span hx-get="/xhr/se/votes/?section=e&dnv={$event_id}" hx-swap="innerHTML" hx-trigger="load, update_votings_{$event_id} from:body">0</span>
            </button>
        </div>
    {/if}

</div>