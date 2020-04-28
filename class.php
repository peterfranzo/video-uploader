public function Read( $Length = 1400 )
		{
			switch( $this->Socket->Engine )
			{
				case SourceQuery :: GOLDSOURCE:
				{
					// GoldSource RCON has same structure as Query
					$this->Socket->Read( );
					
					if( $this->Buffer->GetByte( ) != SourceQuery :: S2A_RCON )
					{
						return false;
					}
					
					$Buffer  = $this->Buffer->Get( );
					$Trimmed = Trim( $Buffer );
					
					if( $Trimmed == 'Bad rcon_password.'
					||  $Trimmed == 'You have been banned from this server.' )
					{
						throw new SourceQueryException( $Trimmed );
					}
					
					$ReadMore = false;
					
					// There is no indentifier of the end, so we just need to continue reading
					// TODO: Needs to be looked again, it causes timeouts
					do
					{
						$this->Socket->Read( );
						
						$ReadMore = $this->Buffer->Remaining( ) > 0 && $this->Buffer->GetByte( ) == SourceQuery :: S2A_RCON;
						
						if( $ReadMore )
						{
							$Packet  = $this->Buffer->Get( );
							$Buffer .= SubStr( $Packet, 0, -2 );
							
							// Let's assume if this packet is not long enough, there are no more after this one
							$ReadMore = StrLen( $Packet ) > 1000; // use 1300?
						}
					}
					while( $ReadMore );
					
					$this->Buffer->Set( Trim( $Buffer ) );
					
					break;
				}
				case SourceQuery :: SOURCE:
				{
					$this->Buffer->Set( FRead( $this->RconSocket, $Length ) );
					
					$Buffer = "";
					
					$PacketSize = $this->Buffer->GetLong( );
					
					$Buffer .= $this->Buffer->Get( );
					
					// TODO: multi packet reading
					
					$this->Buffer->Set( $Buffer );
					
					break;
				}
			}
		}
