<div class="tab-pane" id="tab4">
    <div class="card-header mb-4"><div class="card-title">Security</div></div>
    <div class="col-lg-6 fl">
        <div class="form-group">
            {{Form::label('password','New Password',['class'=>''])}}
            {{ Form::password('security[password]', array('id'=>'password','placeholder'=>'Password', 'class'=>'form-control','data-strength' ) ) }}

            <span class="error"></span>
        </div>
    </div>
    <div class="col-lg-6 fl">
        <div class="form-group">
            {{Form::label('c_password','Confirm Password',['class'=>''])}}
            {{ Form::password('security[c_password]', array('id'=>'c_password','placeholder'=>'Confirm Password', 'class'=>'form-control' ) ) }}

            <span class="error"></span>
        </div>
    </div> 
    
</div>
                        