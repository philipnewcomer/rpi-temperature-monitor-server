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
							<span v-if="readings.length">{{ readings[0].temperature }}&deg;</span>
							<span v-else="readings.length">&nbsp;</span>
						</h1>
					</div>

					<div class="card-footer">
						<button type="button" class="btn btn-primary btn-sm" v-on:click="getReadings" :disabled="!readings.length">
							Refresh
						</button>
					</div>
				</div>
			</div>
		</div>

		<div class="row mt-4 justify-content-center">
			<div class="col col-md-8 col-lg-6">
				<table class="table table-bordered table-striped">
					<thead>
						<tr>
							<th scope="col">Date</th>
							<th scope="col" class="text-right">Reading</th>
						</tr>
					</thead>

					<tbody>
						<tr v-for="reading in readings" v-if="readings.length">
							<td>
								{{ reading.timestamp }}
							</td>
							<td class="text-right">
								{{ reading.temperature }}
							</td>
						</tr>

						<tr v-if="!readings.length">
							<td colspan="2">
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
			'readingsUrl'
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
			}
		},

		mounted() {
			this.getReadings();
		}
	}
</script>
