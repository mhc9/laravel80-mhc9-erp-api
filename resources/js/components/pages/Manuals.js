import React from 'react'

const Manuals = () => {
    return (
        <section className="manuals container">
            <h1 className="title">คู่มือการให้บริการ</h1>

            <hr className="my-2" />

            <div className="manuals-wrapper">
                <a href="https://www.mhc9dmh.com/DATA/File/1-people_service.pdf">
                    <div className="manual">
                        <h3>1. คู่มือการให้บริการประชาชน</h3>
                        <span><i className="fas fa-angle-down"></i></span>
                    </div>
                </a>
                <a href="https://dmh.go.th/Download/Guide/view.asp?id=831" target="_blank">
                    <div className="manual">
                        <h3>3. คู่มือร้องเรียนกรมสุขภาพจิต ปี 2563</h3>
                        <span><i className="fas fa-angle-down"></i></span>
                    </div>
                </a>
                <a href="https://www.mhc9dmh.com/DATA/File/2-mhc9_wifi_manual.pdf">
                    <div className="manual">
                        <h3>2. คู่มือการใช้งานระบบเครือข่ายไร้สาย (Wi-Fi) ศูนย์สุขภาพจิตที่ 9</h3>
                        <span><i className="fas fa-angle-down"></i></span>
                    </div>
                </a>
            </div>
        </section>
    )
}

export default Manuals