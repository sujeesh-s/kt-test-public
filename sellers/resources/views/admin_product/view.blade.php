<link href="{{URL::asset('admin/assets/plugins/quill/quill.snow.css')}}" rel="stylesheet">
<link href="{{URL::asset('admin/assets/plugins/quill/quill.bubble.css')}}" rel="stylesheet">

<div class="page-header">
    <div class="page-leftheader">
        <h4 class="page-title mb-0">{{$title}}</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#"><i class="fe fe-grid mr-2 fs-14"></i>Product Management</a></li>
            <li class="breadcrumb-item"><a id="bc_list" href="">Admin Product List</a></li>
            <li class="breadcrumb-item active" aria-current="page"><a href="#">{{$title}}</a></li>
        </ol>
    </div>
</div>
<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="card">
            <div class="p-4">
                    <?php // echo '<pre>'; print_r($product); echo '</pre>'; die; ?>
                <div class="col-md-6 fl">
                    <div class="form-group">
                        <div class="text-muted">Product Name</div><div class="font-weight-bold">{{$product->name}}</div>
                    </div>
                </div>
                <div class="col-md-6 fl">
                    <div class="form-group">
                        <div class="text-muted">Category</div><div class="font-weight-bold">{{$product->category->cat_name}}</div>
                    </div>
                </div>
                <div class="col-md-6 fl">
                    <div class="form-group">
                        <div class="text-muted">Sub Category</div><div class="font-weight-bold">{{$product->subCategory->subcategory_name}}</div>
                    </div>
                </div>
                <div class="col-md-6 fl">
                    <div class="form-group">
                        <div class="text-muted">Brand</div><div class="font-weight-bold"> @if($product->brand_id != NULL) {{$product->brand->name}} @else -- @endif</div>
                    </div>
                </div>
                <div class="col-md-6 fl">
                    <div class="form-group">
                        <div class="text-muted">Short Description</div><div class="font-weight-bold"><?php echo getContent($product->short_desc,2); ?></div>
                    </div>
                </div>
                <div class="col-md-6 fl">
                    <div class="form-group">
                        <div class="text-muted">Long Description</div><div class="font-weight-bold"><?php echo getContent($product->desc,2); ?></div>
                    </div>
                </div>
                <div class="col-md-6 fl">
                    <div class="form-group">
                        <div class="text-muted">Content</div><div class="font-weight-bold"><?php echo getContent($product->content,2); ?></div>
                    </div>
                </div>
                <div class="col-md-12 fl">
                    <div class="form-group">
                        <div class="text-muted">Specification</div><div class="font-weight-bold">
                        <div id="quillEditor" ></div>
                        <?php $default_lang =DB::table('glo_lang_lk')->where('is_active', 1)->first();
            $content_specification=DB::table('cms_content')->where('cnt_id', $product->spec_cnt_id)->where('lang_id', $default_lang->id)->first();?>
            @if($content_specification)
            {{Form::hidden('specification',$content_specification->content,['id'=>'specification','class'=>'form-control  '])}}
            @endif
                            </div>
                    </div>
                </div>
                <div class="col-md-12 fl">
                    <div class="form-group">
                        <div class="text-muted">Product Image</div>
                        <div class="font-weight-bold">
                           <?php $image_product =DB::table('prd_admin_images')->where('prd_id', $product->id)->get(); ?>
                            @if($image_product)
                            @foreach($image_product as $imgprd)
                            <div class="d-flex">
                            <span class="pip">
                            <img src="{{ config('app.storage_url').$imgprd->image }}" alt="{{ $imgprd->image }}" class="imageThumb">
                            <input type="hidden" value="{{ $imgprd->id }}" name="image_file">
                            
                            </span>
                            </div>
                            @endforeach
                            @endif
                            </div>
                    </div>
                </div>
                <div class="clr"></div>
                <div class="col-lg-12">
                    <div class="card-footer text-right">
                        <button id="cancel_btn" type="button" class="btn btn-secondary">Back</button>
                        @if(!$isAdded) <button id="add_btn" data-val="{{$product->id}}" type="button" class="btn btn-primary">Add To My Products</button> @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{URL::asset('admin/assets/plugins/quill/quill.min.js')}}"></script>
<script type="text/javascript">
     
$(function() {
    'use strict'
    var icons = Quill.import('ui/icons');
    icons['bold'] = '<i class="fa fa-bold" aria-hidden="true"><\/i>';
    icons['italic'] = '<i class="fa fa-italic" aria-hidden="true"><\/i>';
    icons['underline'] = '<i class="fa fa-underline" aria-hidden="true"><\/i>';
    icons['strike'] = '<i class="fa fa-strikethrough" aria-hidden="true"><\/i>';
    icons['list']['ordered'] = '<i class="fa fa-list-ol" aria-hidden="true"><\/i>';
    icons['list']['bullet'] = '<i class="fa fa-list-ul" aria-hidden="true"><\/i>';
    icons['link'] = '<i class="fa fa-link" aria-hidden="true"><\/i>';
    icons['image'] = '<i class="fa fa-image" aria-hidden="true"><\/i>';
    icons['video'] = '<i class="fa fa-film" aria-hidden="true"><\/i>';
    icons['code-block'] = '<i class="fa fa-code" aria-hidden="true"><\/i>';
    var toolbarOptions = [
        [{
            'header': [1, 2, 3, 4, 5, 6, false]
        }],
        ['bold', 'italic', 'underline', 'strike'],
        [{
            'list': 'ordered'
        }, {
            'list': 'bullet'
        }],
        ['link', 'image', 'video']
    ];
    const editor = new Quill('#quillEditor', {
      bounds: '#quillEditor',
      modules: {
            toolbar: toolbarOptions
        },
         readOnly: true,
      placeholder: 'Product Specification',
      theme: 'snow'
    });

      /**
       * Step1. select local image
       *
       */
    function selectLocalImage() {
      const input = document.createElement('input');
      input.setAttribute('type', 'file');
      input.click();

      // Listen upload local image and save to server
      input.onchange = () => {
        const file = input.files[0];

        // file type is only image.
        if (/^image\//.test(file.type)) {
          saveToServer(file);
        } else {
          alert('Please select an image.');
        }
      };
    }

    /**
     * Step2. save to server
     *
     * @param {File} file
     */
    function saveToServer(file) {
      const fd = new FormData();
      fd.append('image', file);

      const xhr = new XMLHttpRequest();


      xhr.open('POST', "{{ url('/admin/editor-image') }}", true);
      // xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
var csrfToken = "{{ csrf_token() }}";
xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
      xhr.onload = () => {
        if (xhr.status === 200) {
          // this is callback data: url
          // const url = JSON.parse(xhr.responseText).data;
          console.log(xhr.responseText);
          // console.log(url);
          insertToEditor(xhr.responseText);
        }
      };
      xhr.send(fd);
    }

    /**
     * Step3. insert image url to rich editor.
     *
     * @param {string} url
     */
    function insertToEditor(url) {
      // push image url to rich editor.
      const range = editor.getSelection();
      editor.insertEmbed(range.index, 'image', `${url}`);
    }

    // quill editor add image handler
    editor.getModule('toolbar').addHandler('image', () => {
      selectLocalImage();
    });

editor.setContents(JSON.parse($('#specification').val()), 'api');

});

 </script>
