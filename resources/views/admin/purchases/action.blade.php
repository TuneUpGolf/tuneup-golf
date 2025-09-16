<div class="action-btn-fix-wraper">
    @php $user = Auth::user(); @endphp
    @if (
        $purchase->status !== 'complete' &&
            $purchase->lesson->payment_method != 'cash' &&
            $user->type == 'Student' &&
            $purchase->type != 'package')
        @can('create-purchases')
            {!! Form::open([
                'method' => 'POST',
                'class' => 'd-flex',
                'route' => ['purchase-confirm-redirect', ['purchase_id' => $purchase->id]],
                'id' => 'confirm-form-' . $purchase->id,
            ]) !!}
            {{ Form::button(__('Make Payment'), ['type' => 'submit', 'class' => 'btn btn-sm small btn btn-info action-btn-fix']) }}
            <i class="ti ti-eye text-white"></i>
            </a>
            {!! Form::close() !!}
        @endcan
    @endif
    @if ($purchase->type === 'inPerson')
        <button class="btn btn-sm small btn btn-dark" data-lesson_id="{{ $purchase->lesson_id }}" data-tenant_id="{{ $purchase->tenant_id }}" id="preSetActionButton" type="button" data-bs-toggle="tooltip"
            data-bs-placement="bottom" data-bs-original-title="{{ __('View Students') }}">
            <svg width="800px" height="800px" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M512 128a176 176 0 1 0 0 352 176 176 0 0 0 0-352zM160 800c0-176 144-320 352-320s352 144 352 320v48H160v-48z"
                    fill="#FFFFFF" />
            </svg>

        </button>
    @endif
    @if (in_array($user->type, ['Student', 'Instructor']) &&
            ($purchase->status == 'complete' || $purchase->lesson->payment_method == 'cash' || $hasBooking) &&
            $purchase->lesson->type != 'online')
        <a class="btn btn-sm small btn btn-info action-btn-fix"
            href="{{ route('slot.view', ['lesson_id' => $purchase->lesson_id]) }}" data-bs-toggle="tooltip"
            data-bs-placement="bottom" data-bs-original-title="{{ __('Manage Slots') }}">
            <svg width="800px" height="800px" viewBox="0 0 1024 1024" class="icon" version="1.1"
                xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M864 512a32 32 0 0 0-32 32v96a32 32 0 0 0 64 0v-96a32 32 0 0 0-32-32zM881.92 389.44a23.68 23.68 0 0 0-5.76-2.88 19.84 19.84 0 0 0-6.08-1.92 32 32 0 0 0-28.8 8.64A32 32 0 0 0 832 416a32 32 0 1 0 64 0 33.6 33.6 0 0 0-9.28-22.72z"
                    fill="#FFFFFF" />
                <path
                    d="M800 128h-32a96 96 0 0 0-96-96H352a96 96 0 0 0-96 96H224a96 96 0 0 0-96 93.44v677.12A96 96 0 0 0 224 992h576a96 96 0 0 0 96-93.44V736a32 32 0 0 0-64 0v162.56a32 32 0 0 1-32 29.44H224a32 32 0 0 1-32-29.44V221.44A32 32 0 0 1 224 192h32a96 96 0 0 0 96 96h320a96 96 0 0 0 96-96h32a32 32 0 0 1 32 29.44V288a32 32 0 0 0 64 0V221.44A96 96 0 0 0 800 128z m-96 64a32 32 0 0 1-32 32H352a32 32 0 0 1-32-32V128a32 32 0 0 1 32-32h320a32 32 0 0 1 32 32z"
                    fill="#FFFFFF" />
                <path
                    d="M712.32 426.56L448 721.6l-137.28-136.32A32 32 0 0 0 265.6 630.4l160 160a32 32 0 0 0 22.4 9.6 32 32 0 0 0 23.04-10.56l288-320a32 32 0 0 0-47.68-42.88z"
                    fill="#FFFFFF" />
            </svg>
        </a>
    @endif

    {{-- @if ($purchase->status == 'complete' && $purchase->lesson->lesson_quantity !== $purchase->lessons_used && $user->type == 'Student' && $purchase->lesson->type === 'online')
    @can('manage-purchases')
        <a class="btn btn-sm small btn btn-warning "
            href="{{ route('purchase.video.index', ['purchase_id' => $purchase->id]) }}" data-bs-toggle="tooltip"
            data-bs-placement="bottom" data-bs-original-title="{{ __('Add Video') }}">
            <i class="ti ti-plus text-white"></i>
        </a>
    @endcan
@endif --}}
    @if ($user->type == 'Instructor')
        @can('manage-purchases')
            <a class="btn btn-sm small btn btn-warning action-btn-fix"
                href="{{ route('purchase.feedback.create', ['purchase_id' => $purchase->id]) }}" data-bs-toggle="tooltip"
                data-bs-placement="bottom" data-bs-original-title="{{ __('Provide Feedback') }}">
                <i class="ti ti-plus text-white"></i>
            </a>
        @endcan
    @endif
    @if (in_array($user->type, ['Student', 'Instructor']) && ($purchaseVideo = $purchase->videos->first()))
        @can('manage-purchases')
            <a class="btn btn-sm small btn btn-warning action-btn-fix"
                href="{{ route('purchase.feedback.index', ['purchase_id' => $purchase->id]) }}" data-bs-toggle="tooltip"
                data-bs-placement="bottom" data-bs-original-title="{{ __('View Feedback') }}">
                <i class="ti ti-eye text-white"></i>
            </a>
        @endcan
    @endif
    {{-- @if ($purchase->status == 'incomplete' && $user->type == 'Instructor')
    @can('manage-purchases')
        <svg
        data-bs-toggle="tooltip"
            data-bs-placement="bottom" data-bs-original-title="{{ __('Payment incomplete') }}"
        viewBox="0 0 2933.3333 2933.3333"
        height="25"
        width="25"
        xml:space="preserve"
        id="svg2"
        version="1.1">
        <metadata
            id="metadata8">
            <rdf:RDF>
                <cc:Work
                    rdf:about="">
                    <dc:format>image/svg+xml</dc:format>
                    <dc:type
                    rdf:resource="http://purl.org/dc/dcmitype/StillImage" />
                </cc:Work>
            </rdf:RDF>
        </metadata>
        <defs
            id="defs6" />
        <g
            transform="matrix(1.3333333,0,0,-1.3333333,0,2933.3333)"
            id="g10">
            <g
                transform="scale(0.1)"
                id="g12">
                <g
                    transform="scale(1.11633)"
                    id="g14">
                    <path
                    id="path16"
                    style="fill:#ffa21d;fill-opacity:1;fill-rule:nonzero;stroke:none"
                    d="m 15410.3,3994.18 c -46.9,-278.69 -231.7,-573.67 -544.6,-711.98 v 1583.41 c 4.3,-1.61 8.5,-3.05 12.8,-4.66 593.3,-209.71 557.1,-716.46 531.8,-866.77 z m -1314.4,2036.41 c -77.5,44.97 -148.4,91.19 -209.3,139.03 -119.9,94.06 -172.4,288.27 -133.4,494.75 32.7,173.6 139.5,379.01 342.7,483.64 V 6030.59 Z m 1039.2,-443.78 c -88.6,31.27 -179,63.96 -269.4,97.82 v 1532.89 c 200.3,-54.55 302.5,-155.42 309.7,-162.85 139.9,-158.83 382,-174.95 541.7,-35.75 160.3,139.66 177,382.78 37.3,543.04 -145.7,167.24 -452.3,378.02 -888.7,441.53 v 272.15 c 0,212.57 -172.3,384.92 -384.9,384.92 -212.6,0 -384.9,-172.35 -384.9,-384.92 v -316.85 c -39.9,-9.76 -80.1,-20.33 -121.1,-32.69 -501.8,-151.13 -876.5,-580.03 -978.1,-1119.21 -92.9,-493.14 66.1,-969.43 414.9,-1243.01 175.4,-137.5 391.5,-263.54 684.3,-395.58 V 3213.13 c -238.7,23.56 -401.8,90.93 -670.4,266.59 -177.9,116.45 -416.4,66.47 -532.8,-111.35 -116.4,-177.9 -66.6,-416.45 111.4,-532.81 426.3,-278.96 722.2,-368.72 1091.8,-394.34 v -324.18 c 0,-212.58 172.3,-384.93 384.9,-384.93 212.6,0 384.9,172.35 384.9,384.93 v 358.85 c 731.7,166.35 1201.6,782.75 1303.8,1390.64 130.5,776.74 -275.5,1452.08 -1034.4,1720.28 v 0" />
                </g>
                <g
                    transform="scale(1.34969)"
                    id="g18">
                    <path
                    id="path20"
                    style="fill:#ffa21d;fill-opacity:1;fill-rule:nonzero;stroke:none"
                    d="m 12002.1,955.107 c -1843.2,0 -3342.73,1499.523 -3342.73,3342.763 0,1843.23 1499.53,3342.75 3342.73,3342.75 1843.3,0 3342.8,-1499.52 3342.8,-3342.75 0,-1843.24 -1499.5,-3342.763 -3342.8,-3342.763 z m 0,7640.623 C 9632.34,8595.73 7704.27,6667.66 7704.27,4297.87 7704.27,1927.99 9632.34,0 12002.1,0 14372,0 16300,1927.99 16300,4297.87 c 0,2369.79 -1928,4297.86 -4297.9,4297.86" />
                </g>
                <g
                    transform="scale(1.34969)"
                    id="g22">
                    <path
                    id="path24"
                    style="fill:#ffa21d;fill-opacity:1;fill-rule:nonzero;stroke:none"
                    d="m 4138.67,8500.23 v -446.4 c -652.84,85.35 -1252.02,343.56 -1750.28,726.83 l 314.37,314.32 c 186.47,186.5 186.47,488.85 0,675.35 -186.51,186.47 -488.86,186.47 -675.36,0 l -314.34,-314.36 c -383.28,498.26 -641.43,1097.43 -726.843,1750.23 h 446.393 c 263.72,0 477.55,213.9 477.55,477.6 0,263.7 -213.83,477.5 -477.55,477.5 H 986.217 c 85.413,652.9 343.563,1252.1 726.843,1750.3 l 314.34,-314.4 c 186.5,-186.4 488.85,-186.4 675.36,0 186.47,186.5 186.47,488.9 0,675.4 l -314.37,314.3 c 498.26,383.3 1097.44,641.5 1750.28,726.9 v -446.4 c 0,-263.7 213.82,-477.6 477.54,-477.6 263.72,0 477.54,213.9 477.54,477.6 v 446.4 c 652.84,-85.4 1252.02,-343.6 1750.28,-726.9 l -314.36,-314.3 c -186.47,-186.5 -186.47,-488.9 0,-675.4 186.5,-186.4 488.85,-186.4 675.35,0 l 314.32,314.4 c 383.27,-498.2 641.4,-1097.4 726.83,-1750.3 h -446.4 c -263.69,0 -477.5,-213.8 -477.5,-477.5 0,-263.7 213.81,-477.6 477.5,-477.6 h 446.4 c -85.35,-652.8 -343.56,-1251.97 -726.83,-1750.23 l -314.32,314.36 c -186.5,186.47 -488.85,186.47 -675.35,0 -186.47,-186.5 -186.47,-488.85 0,-675.35 l 314.36,-314.32 C 6345.77,8397.39 5746.59,8139.26 5093.75,8053.83 v 446.4 c 0,263.69 -213.82,477.51 -477.54,477.51 -263.72,0 -477.54,-213.82 -477.54,-477.51 z m 5093.72,3183.57 c 0,2545.4 -2070.81,4616.2 -4616.18,4616.2 C 2070.84,16300 0,14229.2 0,11683.8 0,9138.42 2070.84,7067.61 4616.21,7067.61 c 2545.37,0 4616.18,2070.81 4616.18,4616.19" />
                </g>
                <g
                    transform="scale(1.1388)"
                    id="g26">
                    <path
                    id="path28"
                    style="fill:#ffa21d;fill-opacity:1;fill-rule:nonzero;stroke:none"
                    d="m 6980.32,13281.5 c 312.56,0 565.98,253.4 565.98,566 0,312.5 -253.42,565.9 -565.98,565.9 H 6037.04 V 15734 c 0,312.6 -253.42,566 -565.98,566 -312.55,0 -565.96,-253.4 -565.96,-566 v -1886.5 c 0,-312.6 253.41,-566 565.96,-566 h 1509.26" />
                </g>
                <g
                    transform="scale(1.27447)"
                    id="g30">
                    <path
                    id="path32"
                    style="fill:#ffa21d;fill-opacity:1;fill-rule:nonzero;stroke:none"
                    d="m 11362,13553.4 h 2360 c 278.9,0 505.7,-226.9 505.7,-505.7 v -1139.2 l -148.1,148.2 c -197.5,197.5 -517.7,197.5 -715.2,0 -197.5,-197.5 -197.5,-517.7 0,-715.2 L 14375.8,10330 c 197.5,-197.5 517.7,-197.5 715.2,0 l 1011.5,1011.5 c 197.5,197.5 197.5,517.7 0,715.2 -197.5,197.5 -517.7,197.5 -715.2,0 l -148.1,-148.2 v 1139.2 c 0,836.5 -680.6,1517.2 -1517.2,1517.2 h -2360 c -279.4,0 -505.8,-226.5 -505.8,-505.8 0,-279.3 226.4,-505.7 505.8,-505.7" />
                </g>
                <path
                    id="path34"
                    style="fill:#ffa21d;fill-opacity:1;fill-rule:nonzero;stroke:none"
                    d="m 7545.63,4967.5 c -251.72,251.7 -659.81,251.7 -911.53,0 -251.68,-251.7 -251.68,-659.8 0,-911.5 l 188.83,-188.8 H 5371.09 c -355.39,0 -644.53,289.1 -644.53,644.5 v 3007.8 c 0,356 -288.59,644.6 -644.53,644.6 -355.94,0 -644.53,-288.6 -644.53,-644.6 V 4511.7 c 0,-1066.2 867.42,-1933.6 1933.59,-1933.6 h 1451.8 l -188.83,-188.8 c -251.72,-251.7 -251.72,-659.8 0,-911.5 251.72,-251.7 659.81,-251.7 911.53,0 l 1289.06,1289.1 c 251.68,251.7 251.68,659.8 0,911.5 L 7545.63,4967.5" />
            </g>
        </g>
        </svg>
    @endcan
@endif --}}

    @can('edit-purchase')
        <a class="btn btn-sm small btn btn-warning action-btn-fix" href="{{ route('purchase.edit', $purchase->id) }}"
            data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="{{ __('Edit') }}">
            <i class="ti ti-edit text-white"></i>
        </a>
    @endcan
    @can('delete-purchase')
        {!! Form::open([
            'method' => 'DELETE',
            'class' => 'd-flex',
            'route' => ['purchase.destroy', $purchase->id],
            'id' => 'delete-form-' . $purchase->id,
        ]) !!}
        <a href="javascript:void(0);" class="btn btn-sm small btn btn-danger show_confirm action-btn-fix"
            data-bs-toggle="tooltip" data-bs-placement="bottom" id="delete-form-1"
            data-bs-original-title="{{ __('Delete') }}">
            <i class="ti ti-trash text-white"></i>
        </a>
        {!! Form::close() !!}
    @endcan
</div>
