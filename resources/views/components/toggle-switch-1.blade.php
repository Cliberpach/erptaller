
<label class="toggle-switch-1">
    <input type="checkbox" id="{{ $id }}" name="{{ $name }}" {{ $checked ? 'checked' : '' }}>
    <div>
        <span class="on">On</span>
        <span class="off">Off</span>
    </div>
    <i></i>
</label>

@once
    @push('styles')
        <style>
            .toggle-switch-1 {
                transform: scale(1);
                display: block;
                width: 90px;
                height: 40px;
                position: relative;
                cursor: pointer;
                background: linear-gradient(to bottom, #9e9e9e 30%, #f4f4f4);
                border-radius: 20px;
                box-shadow: 0 2px 0 #fff, 0 -2px 0 #969494;
            }

            .toggle-switch-1 input {
                display: none;
            }

            .toggle-switch-1 div {
                display: block;
                width: 65px;
                height: 26px;
                background: linear-gradient(to bottom, #8b8c8e 20%, #f4f4f4);
                border-radius: 13px;
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
            }

            .toggle-switch-1 div:after {
                content: "";
                position: absolute;
                width: 61px;
                height: 22px;
                left: 2px;
                top: 2px;
                background: #828080;
                border-radius: 11px;
                box-shadow: inset 0 0 15px rgba(0, 0, 0, 0.8);
                transition: .2s;
            }

            .toggle-switch-1 i {
                display: block;
                width: 28px;
                height: 28px;
                position: absolute;
                top: 6px;
                left: 8px;
                border-radius: 50%;
                background: linear-gradient(to top, #9e9e9e 20%, #f4f4f4);
                box-shadow: 0 3px 6px rgba(0, 0, 0, 0.5);
                transition: .25s;
            }

            .toggle-switch-1 i:after {
                content: "";
                width: 22px;
                height: 22px;
                position: absolute;
                left: 3px;
                top: 3px;
                background: #d5d4d4;
                border-radius: 50%;
            }

            .toggle-switch-1 input:checked ~ i {
                left: 52px;
            }

            .toggle-switch-1 input:checked + div:after {
                background: #f7931e;
                box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.6);
            }

            .toggle-switch-1 .on,
            .toggle-switch-1 .off {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                font-size: .7em;
                font-weight: bold;
                pointer-events: none;
                text-transform: uppercase;
                transition: .25s;
            }

            .toggle-switch-1 .on {
                left: 10px;
                color: transparent;
            }

            .toggle-switch-1 .off {
                right: 10px;
                color: #444;
            }

            .toggle-switch-1 input:checked + div .on {
                color: #c6631d;
            }

            .toggle-switch-1 input:checked + div .off {
                color: transparent;
            }
        </style>
    @endpush
@endonce
