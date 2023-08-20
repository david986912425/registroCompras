<!DOCTYPE html>
<html>
<head>
<style>
  table {
    border-collapse: collapse;
    width: 100%;
  }
  th, td {
    border: 1px solid black;
    padding: 8px;
    text-align: left;
  }
</style>
</head>
<body>

<h2>Comprobante</h2>

<table>
  <tr>
    <th>fechaEmision</th>
    <th>nameEmisor</th>
    <th>rucEmisor</th>
    <th>nameReceptor</th>
    <th>rucReceptor</th>
    <th>ventaTotal</th>
    <th>ventaTotalImpuesto</th>
    <th>otrosPagos</th>
    <th>importeTotal</th>
  </tr>
  <tr>
    <td>{{ $comprobante->fechaEmision }}</td>
    <td>{{ $comprobante->nameEmisor }}</td>
    <td>{{ $comprobante->rucEmisor }}</td>
    <td>{{ $comprobante->nameReceptor }}</td>
    <td>{{ $comprobante->rucReceptor }}</td>
    <td>{{ $comprobante->ventaTotal }}</td>
    <td>{{ $comprobante->ventaTotalImpuesto }}</td>
    <td>{{ $comprobante->otrosPagos }}</td>
    <td>{{ $comprobante->importeTotal }}</td>
  </tr>
</table>

<h2>Items</h2>
<table>
  <tr>
    <th>productoName</th>
    <th>productoPrecio</th>
  </tr>
  @foreach ($items as $item)
    <tr>
      <td>{{ $item['productoName'] }}</td>
      <td>{{ $item['productoPrecio'] }}</td>
    </tr>
  @endforeach
</table>
</body>
</html>
