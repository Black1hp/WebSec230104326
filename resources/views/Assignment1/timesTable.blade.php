@extends('../layouts.app')
@section('title', 'Multiplication Table')
@section('content')
<body>
<div class="container py-5 mb-5">
    <h1 class="text-center mb-4">Interactive Multiplication Table</h1>

    <div class="table-responsive">
        <table class="table table-bordered" id="multiplicationTable">
            <thead class="table-dark">
            <tr>
                <th>First Number</th>
                <th>Second Number</th>
                <th>Product</th>
            </tr>
            </thead>
            <tbody id="tableBody">
            </tbody>
        </table>
    </div>
</div>

<div class="fixed-bottom bg-white bg-opacity-75 py-2 text-center" id="credit">
    <p class="mb-0">Mohamed Saied<br>230104326</p>
</div>

<script>
    function addRow(num1 = '', num2 = '', product = '') {
        const tbody = document.getElementById('tableBody');
        const newRow = document.createElement('tr');

        const cell1 = document.createElement('td');
        const input1 = document.createElement('input');
        input1.type = 'number';
        input1.className = 'form-control';
        input1.value = num1;
        cell1.appendChild(input1);

        const cell2 = document.createElement('td');
        const input2 = document.createElement('input');
        input2.type = 'number';
        input2.className = 'form-control';
        input2.value = num2;
        cell2.appendChild(input2);

        const cell3 = document.createElement('td');
        cell3.className = 'align-middle';
        cell3.textContent = product;

        newRow.appendChild(cell1);
        newRow.appendChild(cell2);
        newRow.appendChild(cell3);

        input1.addEventListener('input', handleInput);
        input2.addEventListener('input', handleInput);

        tbody.appendChild(newRow);
    }

    function handleInput(event) {
        const row = event.target.closest('tr');
        const inputs = row.querySelectorAll('input');
        const productCell = row.querySelector('td:last-child');

        const num1 = parseFloat(inputs[0].value) || '';
        const num2 = parseFloat(inputs[1].value) || '';

        if (num1 !== '' && num2 !== '') {
            productCell.textContent = (num1 * num2).toString();

            const tbody = document.getElementById('tableBody');
            if (row === tbody.lastElementChild) {
                addRow();
            }
        } else {
            productCell.textContent = '';
        }

        const credit = document.getElementById('credit');
        credit.classList.remove('translate-middle-y');
        void credit.offsetWidth;
        credit.classList.add('translate-middle-y');

        setTimeout(() => {
            credit.classList.remove('translate-middle-y');
        }, 300);
    }

    window.onload = function() {
        addRow(6, 1, 6);
        addRow(6, 2, 12);
        addRow(6, 3, 18);
        addRow(6, 4, 24);
        addRow(7, 1, 7);
        addRow(7, 2, 14);
        addRow(7, 3, 21);
        addRow(7, 4, 28);
        addRow();
    };
</script>
</body>
</html>
@endsection
