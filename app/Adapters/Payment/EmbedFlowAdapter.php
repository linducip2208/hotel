<?php

namespace App\Adapters\Payment;

class EmbedFlowAdapter extends RedirectFlowAdapter
{
    // Sama dengan RedirectFlow tapi expected response: client_token / snap_token untuk embed
}
