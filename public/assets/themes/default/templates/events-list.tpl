<div id="article_list_header">

        <div class="col-sm-12 text-right">
            {if $show_pagination == true}
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-end">
                        <li>
                            <a href="{$pag_prev_href}" aria-label="Previous" class="page-link"><span aria-hidden="true">&laquo;</span></a>
                        </li>
                        {foreach $pagination as $pag}
                            <li class="page-item {$pag.active_class}">
                                <a href="{$pag.href}" class="page-link">{$pag.nbr}</a>
                            </li>
                        {/foreach}
                        <li>
                            <a href="{$pag_next_href}" class="page-link" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>
                        </li>
                    </ul>
                </nav>
            {/if}
        </div>
</div>

{if $show_events_list == true}
{foreach $events as $event => $value}


    <div class="event-list-entry {$value.event_css_classes}">
        {$value.draft_message}
        <div class="row">
            <div class="col-md-2">

                <div class="event-date">
                    <div class="event-date-header">
                        <span class="event-start-day">{$value.event_start_day}.</span>
                        <span class="event-start-month">{$value.event_start_month_text}</span>
                    </div>
                    <span class="event-start-year">{$value.event_start_year}</span>
                    <div class="event-date-footer">
                        <span class="event-end-date">{$value.event_end_day}.{$value.event_end_month}.{$value.event_end_year}</span>
                    </div>
                </div>


            </div>
            <div class="col-md-7">

                <span class="post-author">{$value.event_author}</span>
                <a class="post-headline-link" href="{$value.event_href}"><h3>{$value.event_title}</h3></a>
                {$value.event_teaser}
            </div>
            <div class="col-md-3">
                <div class="teaser-image">
                    <img src="{$value.event_img_src}" class="img-fluid">
                </div>
            </div>
        </div>
        <div class="row mt-1 mb-3">
            <div class="col-md-4">
                {if $value.show_voting == true}

                    <form>
                        {$hidden_csrf_token}
                        <button class="btn btn-sm btn-outline-secondary" hx-post="/api/se/vote/" hx-swap="none" name="vote" value="up-event-{$value.event_id}">
                            <i class="bi bi-hand-thumbs-up-fill"></i>
                            <span class="" hx-get="/api/se/votes/?section=e&upv={$value.event_id}" hx-swap="innerHTML" hx-trigger="load, update_votings_{$value.event_id} from:body">0</span>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" hx-post="/api/se/vote/" hx-swap="none" name="vote" value="dn-event-{$value.event_id}">
                            <i class="bi bi-hand-thumbs-down-fill"></i>
                            <span class="" hx-get="/api/se/votes/?section=e&dnv={$value.event_id}" hx-swap="innerHTML" hx-trigger="load, update_votings_{$value.event_id} from:body">0</span>
                        </button>
                    </form>

                {/if}
            </div>
            <div class="col-md-8 text-end">
                <p class="m-0 post-categories">
                    {foreach $value.event_categories as $category}
                        <a href="{$category.cat_href}" class="btn btn-sm btn-link" title="{$category.cat_title}">{$category.cat_title}</a>
                    {/foreach}
                </p>
                <div class="row">
                    <div class="col-md-8 text-end">

                    </div>
                    <div class="col-md-4 text-end">
                        <a class="btn btn-primary w-100 {$read_more_class}" href="{$value.event_href}">{$btn_read_more}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>


{/foreach}
{else}
    <div class="alert alert-info">
        {$lang_msg_no_entries_found}
    </div>
{/if}

<div id="article_footer">
    {if $show_pagination == true}
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <li>
                <a href="{$pag_prev_href}" aria-label="Previous" class="page-link"><span
                            aria-hidden="true">&laquo;</span></a>
            </li>
            {foreach $pagination as $pag}
                <li class="page-item {$pag.active_class}">
                    <a href="{$pag.href}" class="page-link">{$pag.nbr}</a>
                </li>
            {/foreach}
            <li>
                <a href="{$pag_next_href}" class="page-link" aria-label="Next"><span
                            aria-hidden="true">&raquo;</span></a>
            </li>
        </ul>
    </nav>
    {/if}
</div>