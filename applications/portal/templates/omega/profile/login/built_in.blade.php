<div class="tab-content" ng-show="tab=='built_in'">
    <div class=" element-no-top element-no-bottom" data-os-animation="none" data-os-animation-delay="0s">
        <form id="contactForm" class="contact-form" ng-submit="authenticate('built_in')">
            <div class="form-group form-icon-group">
                <input autoFillSync class="form-control" id="name" name="name" placeholder="Your name *" type="text" required="" ng-model="username">
                <i class="fa fa-user"></i>
            </div>
            <div class="form-group form-icon-group">
                <input autoFillSync class="form-control" id="password" name="password" placeholder="Your password *" type="password" required="" ng-model="password">
                <i class="fa fa-key"></i>
            </div>
            <div>
            	<button type="submit" class="btn btn-primary" data-loading-text="Logging in... Please wait">Login using Built In Account</button>
            </div>
            <div id="messages"></div>
        </form>
    </div>
</div>