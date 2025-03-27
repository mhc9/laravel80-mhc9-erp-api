<table>
    <thead>
        <tr>
            <th style="text-align: center;">ลำดับ</th>
            <th style="text-align: center;">เลขที่สัญญา</th>
            <th style="text-align: center;">เลขที่ฎกา/อ้างอิง</th>
            <th style="text-align: center;">ชื่อ-สกุลผู้ยืม</th>
            <th style="text-align: center;">โครงการ</th>
            <th style="text-align: center;">จำนวนเงินยืม</th>
            <th style="text-align: center;">จำนวนเงิน<br />คืน/เบิกเพิ่ม</th>
            <th style="text-align: center;">วันที่จัดโครงการ</th>
            <th style="text-align: center;">วันที่ส่งหนังสือยืมเงิน</th>
            <th style="text-align: center;">วันที่ ขบ.02</th>
            <th style="text-align: center;">วันที่เงินเข้า</th>
            <th style="text-align: center;">วันที่เคลียร์</th>
            <th style="text-align: center;">วันที่ครบกำหนด</th>
            <th style="text-align: center;">เลขที่ใบรับใบสำคัญ</th>
            <th style="text-align: center;">เลขที่ใบเสร็จ</th>
            <th style="text-align: center;">วันที่ใบเสร็จ</th>
        </tr>
    </thead>
    <tbody>
        <?php $row = 0; ?>
        @foreach($contracts as $contract)
            <tr>
                <td style="text-align: center;">{{ ++$row }}</td>
                <td style="text-align: center;">{{ $contract->contract_no }}</td>
                <td style="text-align: center;">{{ $contract->bill_no }}</td>
                <td>{{ $contract->loan->employee->prefix->name }}{{ $contract->loan->employee->firstname }} {{ $contract->loan->employee->lastname }}</td>
                <td>{{ $contract->loan->project_name }}</td>
                <td style="text-align: right;">{{ number_format($contract->net_total) }}</td>
                <td style="text-align: right;">
                    @if ($contract->refund)
                        {{ number_format($contract->refund->balance) }}
                    @else
                        {{ '-' }}
                    @endif
                </td>
                <td style="text-align: center;">{{ convDbDateToThDate($contract->loan->project_sdate). '-' .convDbDateToThDate($contract->loan->project_edate) }}</td>
                <td style="text-align: center;">{{ convDbDateToThDate($contract->sent_date) }}</td>
                <td style="text-align: center;">{{ convDbDateToThDate($contract->bk02_date) }}</td>
                <td style="text-align: center;">{{ convDbDateToThDate($contract->deposited_date) }}</td>
                <td style="text-align: center;">{{ $contract->refund ? convDbDateToThDate($contract->refund->approved_date) : '' }}</td>
                <td style="text-align: center;">{{ convDbDateToThDate($contract->refund_date) }}</td>
                <td style="text-align: center;">{{ $contract->refund ? $contract->refund->bill_no : '' }}</td>
                <td style="text-align: center;">{{ $contract->refund ? $contract->refund->receipt_no : '' }}</td>
                <td style="text-align: center;">{{ $contract->refund ? convDbDateToThDate($contract->refund->receipt_date) : '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>