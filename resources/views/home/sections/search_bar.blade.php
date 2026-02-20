<section class="py-4">
    <div class="container">
        <div class="wow-ultra">
            <form class="bar" role="search">
                <div class="seg" id="home-template-seg-what">
                    <i class="bi bi-stars fs-5 text-muted"></i>
                    <div class="flex-grow-1">
                        <div class="seg-label">What</div>
                        <input id="home-template-what" type="text" autocomplete="off" placeholder="Massage, yoga, breathwork…" aria-expanded="false" aria-controls="home-template-what-pane">
                    </div>
                    <div id="home-template-what-pane" class="pane narrow d-none" role="listbox" aria-label="What suggestions">
                        <div id="home-template-what-list" class="listy">
                            {{-- static suggestions populated by JS in the mockup --}}
                        </div>
                    </div>
                </div>
                <div class="seg" id="home-template-seg-where">
                    <i class="bi bi-geo-alt fs-5 text-muted"></i>
                    <div class="flex-grow-1">
                        <div class="seg-label">Where</div>
                        <div id="home-template-where-editor" class="editor" contenteditable="true" role="textbox" aria-multiline="false" aria-label="Where"></div>
                        <input id="home-template-where" type="hidden" value="">
                    </div>
                    <div id="home-template-where-pane" class="pane narrow d-none" role="listbox" aria-label="Where suggestions">
                        <div id="home-template-where-list" class="listy"></div>
                    </div>
                </div>
                <div class="seg" id="home-template-seg-when">
                    <i class="bi bi-calendar3 fs-5 text-muted"></i>
                    <div class="flex-grow-1">
                        <div class="seg-label">When</div>
                        <input id="home-template-when" type="text" autocomplete="off" placeholder="Any date" aria-expanded="false" aria-controls="home-template-when-pane">
                    </div>
                    <div id="home-template-when-pane" class="pane narrow d-none" role="dialog" aria-label="When picker"></div>
                </div>
                <div class="seg" id="home-template-seg-who">
                    <i class="bi bi-person-heart fs-5 text-muted"></i>
                    <div class="flex-grow-1">
                        <div class="seg-label">Who</div>
                        <div class="chips">
                            <span class="chip chip-sm">Me</span>
                            <span class="chip chip-sm">Partner</span>
                            <span class="chip chip-sm">Friend</span>
                        </div>
                    </div>
                    <div id="home-template-who-pane" class="pane d-none" role="dialog" aria-label="Who panel">
                        <div class="p-3">
                            <div class="section-title">Group type</div>
                            <div id="home-template-group-type-list">
                                <button type="button" class="item" data-group="Solo" aria-selected="false"><i class="bi bi-person"></i><span class="title">Solo</span></button>
                                <button type="button" class="item" data-group="Couple" aria-selected="true"><i class="bi bi-heart"></i><span class="title">Couple</span></button>
                                <button type="button" class="item" data-group="Group" aria-selected="false"><i class="bi bi-people"></i><span class="title">Group</span></button>
                            </div>
                        </div>
                        <div class="text-end p-3">
                            <button type="button" class="btn btn-primary btn-sm" id="home-template-who-done">Done</button>
                        </div>
                    </div>
                </div>
                <button class="btn-wow is-squarish btn-xl" data-loader-init="1">
                    <span class="btn-label">Search</span>
                    <span class="btn-spinner" aria-hidden="true"><span class="spin"></span></span>
                </button>
            </form>
        </div>
    </div>
</section>

