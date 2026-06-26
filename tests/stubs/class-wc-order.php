<?php

class WC_Order {
	private $id;
	private $meta    = [];
	private $status  = 'pending';
	private $total   = 0.0;
	private $payment = '';

	public function __construct( int $id = 1 ) {
		$this->id = $id;
	}

	public function get_id(): int { return $this->id; }
	public function get_total(): float { return $this->total; }
	public function set_total( float $t ): void { $this->total = $t; }
	public function get_status(): string { return $this->status; }
	public function get_payment_method(): string { return $this->payment; }
	public function set_payment_method( string $m ): void { $this->payment = $m; }

	public function get_meta( string $key, bool $single = true ) {
		return $this->meta[ $key ] ?? '';
	}

	public function update_meta_data( string $key, $value ): void {
		$this->meta[ $key ] = $value;
	}

	public function save(): void {}

	public function payment_complete(): void {
		$this->status = 'processing';
	}

	public function update_status( string $status, string $note = '' ): void {
		$this->status = $status;
	}

	public function add_order_note( string $note ): void {}

	public function get_date_created() {
		return null;
	}
}
