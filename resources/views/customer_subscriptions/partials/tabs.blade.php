{{--
    The three C2 screens share one nav: plans (C2.1), subscribers (C2.2) and
    payments (C2.3). Kept in a partial so a fourth never has to be added in three
    places.
--}}
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link {{ $active === 'plans' ? 'active' : '' }}"
            href="{!! route('customer-subscriptions') !!}">
            {{ trans('lang.customer_subscription_plans') }}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $active === 'subscribers' ? 'active' : '' }}"
            href="{!! route('customer-subscriptions.subscribers') !!}">
            {{ trans('lang.customer_subscription_subscribers') }}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $active === 'payments' ? 'active' : '' }}"
            href="{!! route('customer-subscriptions.payments') !!}">
            {{ trans('lang.customer_subscription_payments') }}
        </a>
    </li>
</ul>
