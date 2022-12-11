@livewire('real-estates-helper-box')
<div class="sidenav-overlay"></div>
<div class="drag-target"></div>

<!-- BEGIN: Footer-->
<footer class="footer footer-static footer-light">
    <p class="clearfix mb-0">
        <span class="float-md-start d-block d-md-inline-block mt-25">حقوق الملكية &copy; 2022
            <a class="ms-25" href="#" target="_blank"></a>
            <span class="d-none d-sm-inline-block">, جميع الحقوق محفوظة</span>
        </span>
        <span class="float-md-end d-none d-md-block">صُنع بحب
            <i data-feather="heart"></i>
        </span>
    </p>
</footer>

<button class="btn btn-primary btn-icon scroll-top" type="button"><i data-feather="arrow-up"></i></button>
<!-- END: Footer-->

@include('partials.scripts.vendorjs')
@stack('order-create')
@stack('order-update')
</body>

</html>
