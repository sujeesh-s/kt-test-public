@php $currency = getCurrency()->name; @endphp
<div class="row flex-lg-nowrap">
    <div class="col-12">
        <div class="row flex-lg-nowrap">
            <div class="col-12 mb-3">
                <div class="e-panel card">
                    <div id="data-content" class="card-body">
                       
                        <div id="table_body" class="card-body table-card-body">
                            <div>
                                    <table id="ticket" class="ticket table table-striped table-bordered w-100 text-nowrap">
                                    <thead>
                                        <tr>
                                            <th class="wd-15p">#</th>
                                            <th class="wd-15p">Ticket ID</th>
                                            <th class="wd-15p">Subject</th>
                                            <th class="wd-15p">Date</th>
                                            <th class="wd-15p">View</th>
                                            <th class="wd-15p">Create</th>
                                            
                                        </tr>
                                    </thead>
                                    <tbody>
                                       @if($list && count($list) > 0) @php $n = 0; @endphp
                                            @foreach($list as $row) @php $n++; @endphp 
                                    <tr>    
                                        <td class="align-middle select-checkbox"></td>
                                        @php $view = $row['support_id']; @endphp
                                        <td>{{$row['ticket_id']}}</td>
                                        <td>{{$row['subject']}}</td>
                                        <td>{{$row['created_at']}}</td>
                                        <td><button id="viewBtn-@php echo $view @endphp" data-toggle="modal" data-target="#chatmodel" class="mr-2 btn btn-success btn-sm viewBtn"><i class="fa fa-eye mr-1"></i>View</button>
                                        </td>
                                        <td><button type="button" id="create-@php echo $view @endphp" class="createbtn btn btn-info btn-sm" data-toggle="modal" data-target="#normalmodal">Reply</button></td>
                                    </tr>
                                    
                                    @endforeach
                                        @endif
                                        </tbody>
                                </table>
                                {{ csrf_field() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
            <!-- Message Modal -->
        <div class="modal fade" id="chatmodel" tabindex="-1" role="dialog"  aria-hidden="true">
            <div class="modal-dialog modal-dialog-right chatbox modal-lg" role="document">
                <div class="modal-content chat border-0">
                    <div class="card overflow-hidden mb-0 border-0 chat_content" id="chat_content">

                    </div>
                </div>
            </div>
        </div>
        <!-- End Page -->

        <!-- Modal -->
            <div class="modal fade" id="normalmodal" tabindex="-1" role="dialog" aria-labelledby="normalmodal" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="normalmodal1">Reply</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body modalbody" id="modal_body">
                            
                        </div>
                        <!-- <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary">Save changes</button>
                        </div> -->
                    </div>
                </div>
            </div>

            <!-- Get support modal -->
            <div class="modal fade" id="getsupport" tabindex="-1" role="dialog" aria-labelledby="getsupport" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="getsupport1">Get support</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <form>
                        <div class="modal-body">
                           <div class="form-group">
                            <label class="form-label">Date</label>
                            <input type="text" readonly name="c_date" value="@php echo date('d-m-Y')@endphp" class="form-control">
                           </div> 
                           <div class="form-group">
                            <label class="form-label">Subject</label>
                            <input type="text" name="s_subject" id="s_subject" class="form-control s_subject" maxlength="200" required>
                            <span class="error error_subject" id="error_subject"><span>
                           </div>
                           <div class="form-group">
                            <label class="form-label">Message</label>
                            <textarea rows="2" name="s_msg" id="s_msg" class="form-control s_msg" required></textarea>
                            <span class="error error_msg" id="error_msg"><span>
                           </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="button"  class="getsupport btn btn-primary">Send</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>

 
 <!--<script src="{{asset('admin/assets/js/datatable/tables/ticket-datatable.js')}}"></script>-->
 
 
 