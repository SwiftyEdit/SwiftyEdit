
<h1>{$post_title}</h1>

{if $post_type == "m"}
    {if $post_tmb_src != ""}
    <div class="teaser-image">
        <img src="{$post_tmb_src}" class="img-fluid">
    </div>
    {/if}

    {$post_teaser}
    {$post_text}

{/if}

<div class="post-body">

    {* post type gallery *}
    {if $post_type == "g"}

        <div class="post-thumbnails clearfix border-1">
            <div class="gallery">
                {if is_array($gallery_thumbs)}
                    {foreach $gallery_thumbs as $thumb}
                        <a href="{$thumb.img_src}" class="post-thumbnail glightbox lightbox" style="background-image: url({$thumb.tmb_src})"></a>
                    {/foreach}
                {/if}
            </div>
        </div>

        {$post_teaser}

    {/if}
    {* post type gallery end *}

    {* post type image *}
    {if $post_type == "i"}
        <div class="card p-3">
            <img src="{$post_tmb_src}" class="img-fluid">
        </div>

        {$post_teaser}

    {/if}
    {* post type image end *}

    {* post type video *}
    {if $post_type == "v"}

        <div class="card p-3">
            <iframe id="video-player"
                    type="text/html"
                    width="100%"
                    height="450px"
                    src="https://www.youtube.com/embed/{$video_id}?rel=0&showinfo=0&color=white&iv_load_policy=3"
                    frameborder="0"
                    allowfullscreen>
            </iframe>
        </div>

        {$post_teaser}

    {/if}
    {* post type video end *}

    {* post type link *}
    {if $post_type == "l"}
        <p>
            <a class="btn btn-primary" href="{$post_external_redirect}" title="{$post_external_link}" target="_blank">{$post_link_text}</a>
        </p>

        {$post_teaser}

    {/if}
    {* post type link end *}

    {* post type file (download) *}
    {if $post_type == "f"}
        <form action="{$form_action}" method="POST">
            <button type="submit" class="btn btn-secondary"><i class="bi bi-arrow-down-circle"></i> {$btn_download} {$post_file_version}</button>
            <input type="hidden" name="post_attachment" value="{$post_file_attachment}">
            <input type="hidden" name="post_attachment_external" value="{$post_file_attachment_external}">
            {$hidden_csrf_token}
            <p class="text-muted">{$post_file_attachment_external} {$post_file_license}</p>
        </form>

        {$post_teaser}

    {/if}
    {* post type file end *}



</div>

<p class="text-end">
    <span class="post-author">{$post_author}</span> <span class="post-releasedate">{$post_releasedate_str}</span>
</p>

{if $show_voting == true}
    <div class="mb-3">
        <form>
            {$hidden_csrf_token}
            <button class="btn btn-sm btn-outline-secondary" hx-post="/api/se/vote/" hx-swap="none" name="vote" value="up-post-{$post_id}">
                <i class="bi bi-hand-thumbs-up-fill"></i>
                <span class="" hx-get="/api/se/votes/?section=b&upv={$post_id}" hx-swap="innerHTML" hx-trigger="load, update_votings_{$post_id} from:body">0</span>
            </button>
            <button class="btn btn-sm btn-outline-secondary" hx-post="/api/se/vote/" hx-swap="none" name="vote" value="dn-post-{$post_id}">
                <i class="bi bi-hand-thumbs-down-fill"></i>
                <span class="" hx-get="/api/se/votes/?section=b&dnv={$post_id}" hx-swap="innerHTML" hx-trigger="load, update_votings_{$post_id} from:body">0</span>
            </button>
        </form>
    </div>
{/if}