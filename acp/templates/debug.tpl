<div class="alert alert-info mx-3">

    <nav>
        <div class="nav nav-tabs" id="debug-tab" role="tablist">
            <button class="nav-link active" id="nav-request-tab" data-bs-toggle="tab" data-bs-target="#nav-request" type="button" role="tab" aria-controls="nav-request" aria-selected="true">$_REQUEST</button>
            <button class="nav-link" id="nav-session-tab" data-bs-toggle="tab" data-bs-target="#nav-session" type="button" role="tab" aria-controls="nav-session" aria-selected="false">$_SESSION</button>
            <button class="nav-link" id="nav-prefs-tab" data-bs-toggle="tab" data-bs-target="#nav-prefs" type="button" role="tab" aria-controls="nav-prefs" aria-selected="false">SE_PREFS</button>

        </div>
    </nav>
    <div class="tab-content" id="nav-tabContent">
        <div class="tab-pane fade show active" id="nav-request" role="tabpanel" aria-labelledby="nav-request-tab" tabindex="0">
            <div class="row">
                <div class="col-6">
                    <h6>$_POST</h6>
                    {post}
                </div>
                <div class="col-6">
                    <h6>$_GET</h6>
                    {get}
                </div>
            </div>

        </div>
        <div class="tab-pane fade" id="nav-session" role="tabpanel" aria-labelledby="nav-session-tab" tabindex="0">
            {session}
        </div>
        <div class="tab-pane fade" id="nav-prefs" role="tabpanel" aria-labelledby="nav-prefs-tab" tabindex="0">
            {prefs}
        </div>
    </div>
</div>