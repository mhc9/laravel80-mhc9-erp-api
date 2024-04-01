<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="{{ asset('css/pdf.css') }}" rel="stylesheet">

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <!-- <script src="{{ asset('js/app.js') }}" defer></script> -->
</head>
<body>
    <div class="container-fluid p-4">
        <div class="row">
            <div class="col-12">
                <div style="position: relative; display: flex; flex-direction: column; justify-content: center; align-items: center;" class="mb-2">
                    <h5>ประเภทรถส่วนกลาง / รถรับรอง / รถรับรองประจำจังหวัด / รถอารักขา</h5>
                    <h5>ทะเบียนรถของศูนย์สุขภาพจิตที่ 9 กรมสุขภาพจิต กระทรวงสาธารณสุข</h5>

                    <div style="position: absolute; top: 0; right: 0;">
                        แบบที่ 2
                    </div>
                </div>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 5%">#</th>
                            <th style="text-align: center; width: 8%">ชื่อรถ</th>
                            <th>แบบ รุ่น ปี ขนาดเครื่องยนต์ (ซีซี)</th>
                            <th style="text-align: center; width: 8%">เลขทะเบียน</th>
                            <th style="text-align: center; width: 10%">สังกัดหน่วยงาน</th>
                            <th style="text-align: center; width: 8%">ราคา <br />(บาท)</th>
                            <th style="text-align: center; width: 10%">วันที่ได้มา <br />(วัน เดือน ปี)</th>
                            <th style="text-align: center; width: 10%">วันที่สิ้นสุดสัญญา (ถ้ามี) <br />(วัน เดือน ปี)</th>
                            <th style="text-align: center; width: 10%">หมายเหตุ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $index = 0; ?>
                        @foreach($cars as $car)
                            <tr>
                                <td style="text-align: center;">{{++$index}}</td>
                                <td>{{$car->CarType}} {{$car->CarId == 2 ? '(รถเช่า)' : ''}}</td>
                                <td>{{$car->CarName}}</td>
                                <td style="text-align: center;">{{$car->CarNum}}</td>
                                <td style="text-align: center;">ศูนย์สุขภาพจิตที่ 9</td>
                                <td style="text-align: right;">{{number_format($car->CarPrice)}}</td>
                                <td style="text-align: center;">{{convDbDateToThDate($car->CarDatein)}}</td>
                                <td style="text-align: center;">{{convDbDateToThDate($car->CarDateEnd)}}</td>
                                <td></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>