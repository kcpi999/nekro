<div class="main-wrap">    
    <div class="l-col">
        <div class="row">
            <span>Long URL:</span>
        </div>
        <div class="row">
            <input type="text" id="long-url" name="long_url" placeholder="paste URL here" maxlength="4000" onClick="this.setSelectionRange(0, this.value.length)" />
            <button id="do" class="button" onclick="make_short_url();">DO!</button>
        </div>
        <div class="row">
            <div class="error" id="error"></div>
        </div>
    </div>
    <div class="r-col">
        <div class="row">
            <span>Short URL:</span>
        </div>
        <div class="short-url">
            <input type="text" id="short-url" readonly="readonly" value="" onClick="this.setSelectionRange(0, this.value.length)" />
        </div>
    </div>    
</div>
