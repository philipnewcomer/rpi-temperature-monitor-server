<template>
	<div class="container">
		<div class="row mt-4 justify-content-center">
			<div class="col col-md-6 col-lg-4">
				<div class="card text-center">
					<div class="card-header">
						<em v-if="readings.length">{{ readings[0].human_time_diff }}</em>
						<em v-else="readings.length">Loading...</em>
					</div>

					<div class="card-body">
						<h1 class="display-1">
							<span :class="getTextClass(readings[0].temperature)" v-if="readings.length">{{ readings[0].temperature }}&deg;</span>
							<span v-else="readings.length">&nbsp;</span>
						</h1>
					</div>

					<div class="card-footer">
						<button type="button" class="btn btn-primary" v-on:click="getReadings" :disabled="!readings.length">
							Refresh
						</button>
					</div>
				</div>
			</div>
		</div>

		<div class="row mt-4 justify-content-center">
			<div class="col col-md-10 col-lg-8">
				<table class="table table-bordered table-striped">
					<thead>
						<tr>
							<th scope="col">Date</th>
							<th scope="col" class="text-right">Reading</th>
							<th scope="col">Remote IP</th>
						</tr>
					</thead>

					<tbody>
						<tr v-for="reading in readings" v-if="readings.length">
							<td>
								{{ reading.timestamp }}
							</td>

							<td class="text-right" :class="getTextClass(reading.temperature)">
								{{ reading.temperature }}
							</td>

							<td>
								<code>{{ reading.remote_ip }}</code>
							</td>
						</tr>

						<tr v-if="!readings.length">
							<td colspan="3">
								No readings.
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		props: [
			'readingsUrl',
			'dangerLevel',
			'warningLevel'
		],

		data() {
			return {
				readings: []
			}
		},

		methods: {
			getReadings() {
				this.readings = [];

				return axios.get(this.readingsUrl)
					.then(response => {
						this.readings = response.data;
					})
					.catch(error => {
						console.log(error);
					})
			},

			getTextClass(temperature) {
				if (temperature < this.dangerLevel) {
					return 'text-danger';
				}

				if (temperature < this.warningLevel) {
					return 'text-warning';
				}

				return 'text-success';
			}
		},

		mounted() {
			this.getReadings();
		}
	}
</script>
