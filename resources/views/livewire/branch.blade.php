<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="dataTables_wrapper dt-bootstrap5 no-footer">



                <div class="card-header border-bottom p-1">
                    <div class="head-label"></div>
                    <div class="btn-group">
                        <button class="btn btn-gradient-warning dropdown-toggle" type="button" id="dropdownMenuButton303"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            تصدير
                        </button>
                        <div class="dropdown-menu text-center export p-0" aria-labelledby="dropdownMenuButton303"
                            style="">

                            <button class="btn export" tabindex="0" wire:click="export('excel')"
                                aria-controls="DataTables_Table_0" type="button">
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-file font-small-4 me-50">
                                        <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z">
                                        </path>
                                        <polyline points="13 2 13 9 20 9"></polyline>
                                    </svg>Excel
                                </span>
                            </button>

                            {{-- <a class="dropdown-item" href="#">Excel</a> --}}
                        </div>
                    </div>
                </div>


                <div class="d-flex justify-content-between align-items-center mx-0 row">
                    <div class="col-sm-12 col-md-6">
                        <div class="col-sm-12 col-md-6">
                            <div class="dataTables_length" id="DataTables_Table_0_length">
                                <label>أظهر
                                    <select wire:model='rows_number' class="form-select">
                                        <option value="all">الكل</option>
                                        <option value="10" selected>10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select> مدخلات
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12 col-md-6">
                        <div class="dataTables_filter">
                            <label>حالة الفرع:
                                <select wire:model='status' class="form-select"
                                    style="margin-left: .5em; display: inline-block; width: auto;">
                                    <option value="all" selected>الكل</option>
                                    <option value="1" selected>نشط</option>
                                    <option value="2" selected>غير نشط</option>
                                </select>
                            </label>
                            <label>ابحث:<input type="search" wire:model='search' class="form-control"
                                    placeholder="اسم الفرع/ كود الفرع"></label>
                        </div>
                    </div>
                </div>

                <div class="mb-2"></div>

                <table class="table dataTable no-footer text-center" role="grid">
                    <thead>
                        <tr role="row">
                            <th class="sorting {{ $style_sort_direction }}" wire:click="sortBy('id')" tabindex="0"
                                rowspan="1" colspan="1" aria-sort="ascending">الترتيب</th>
                            <th tabindex="0" rowspan="1" colspan="1" aria-sort="ascending">اسم الفرع</th>
                            <th tabindex="0" rowspan="1" colspan="1">كود الفرع</th>
                            <th tabindex="0" rowspan="1" colspan="1">المدينة</th>
                            <th tabindex="0" rowspan="1" colspan="1">الحالة</th>
                            <th tabindex="0" rowspan="1" colspan="1">عدد المستخدمين</th>
                            <th tabindex="0" rowspan="1" colspan="1">تحكم</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach ($branches as $branch)
                            <tr class="odd">
                                <td class="sorting_1">{{ $branch->id }}</td>

                                <td>{{ $branch->name }}</td>
                                <td> {{ $branch->code }}</td>
                                <td>{{ $branch->city_name }}</td>
                                <td>
                                    @if ($branch->status == 1)
                                        <span class="badge bg-success">نشط</span>
                                    @else
                                        <span class="badge bg-danger"> غير نشط</span>
                                    @endif
                                </td>

                                <td>{{ getUsersMarketersBranch($branch->id) }} </td>
                                <td>

                                    <a class="item-edit active" data-bs-target="#editBranch" data-bs-toggle="modal"
                                        wire:click='callBranchModal({{ $branch->id }})'>
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <a class="btn item-edit" id="active-alert"
                                        wire:click='updateStatus({{ $branch->id }})' style="padding:0;color:#EA5455 ">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach


                    </tbody>

                </table>

                <div class="d-flex justify-content-between mx-0 row">
                    <div class="col-sm-12 col-md-6">
                        <div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite">
                            إظهار
                            {{ $branches->perPage() }} من اصل {{ $branches->total() }}
                        </div>
                    </div>

                    <div class="col-sm-12 col-md-6">
                        <div class="dataTables_paginate paging_simple_numbers" id="DataTables_Table_0_paginate">
                            <ul class="pagination">
                                {{ $branches->withQueryString()->onEachSide(0)->links() }}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
